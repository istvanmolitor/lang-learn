<?php

namespace App\Services;

use App\Models\Book;
use App\Repositories\Contracts\BookPageRepositoryInterface;
use App\Repositories\Contracts\BookRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\ParagraphRepositoryInterface;
use App\Repositories\Contracts\SentenceRepositoryInterface;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class EpubBookImporter
{
    /**
     * Target character count per page. A chapter's paragraphs are grouped
     * into pages of roughly this size so a page reads like an actual book
     * page instead of a whole chapter at once. Paragraphs are never split
     * across pages, and pages never cross a chapter boundary.
     */
    private const int TARGET_PAGE_CHARACTERS = 1200;

    public function __construct(
        private readonly BookRepositoryInterface $books,
        private readonly BookPageRepositoryInterface $bookPages,
        private readonly ParagraphRepositoryInterface $paragraphs,
        private readonly SentenceRepositoryInterface $sentences,
        private readonly LanguageRepositoryInterface $languages,
    ) {}

    /**
     * Import the given EPUB as a book, splitting each chapter into
     * page-sized chunks of paragraphs and trimmed sentences.
     *
     * Does nothing if a book with this title and language already exists, so
     * the import stays safe to run repeatedly.
     */
    public function import(string $epubPath, string $languageCode = 'en'): Book
    {
        $zip = new ZipArchive;

        throw_if($zip->open($epubPath) !== true, new RuntimeException("Unable to open {$epubPath}."));

        try {
            $opfPath = $this->resolveOpfPath($zip);
            $opf = $this->loadXml($this->readFromZip($zip, $opfPath));
            $opfDirectory = dirname($opfPath);

            $title = $this->resolveTitle($opf);
            $language = $this->languages->findByCodeOrFail($languageCode);

            $existing = Book::query()
                ->where('title', $title)
                ->where('language_id', $language->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $pages = $this->extractPages($zip, $opf, $opfDirectory);

            return DB::transaction(fn () => $this->store($title, $language->id, $pages));
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<int, array<int, string>>  $pages  each page is a list of paragraph texts
     */
    private function store(string $title, int $languageId, array $pages): Book
    {
        $book = $this->books->create([
            'language_id' => $languageId,
            'title' => $title,
        ]);

        $pagePosition = 0;

        foreach ($pages as $paragraphTexts) {
            $sentenceTextsByParagraph = array_values(array_filter(
                array_map($this->splitIntoSentences(...), $paragraphTexts),
                fn (array $sentenceTexts) => $sentenceTexts !== [],
            ));

            if ($sentenceTextsByParagraph === []) {
                continue;
            }

            $page = $this->bookPages->create([
                'book_id' => $book->id,
                'position' => $pagePosition++,
            ]);

            $paragraphPosition = 0;

            foreach ($sentenceTextsByParagraph as $sentenceTexts) {
                $paragraph = $this->paragraphs->create([
                    'page_id' => $page->id,
                    'position' => $paragraphPosition++,
                ]);

                foreach ($sentenceTexts as $sentencePosition => $sentenceText) {
                    $this->sentences->create([
                        'paragraph_id' => $paragraph->id,
                        'position' => $sentencePosition,
                        'text' => $sentenceText,
                    ]);
                }
            }
        }

        return $book;
    }

    private function resolveOpfPath(ZipArchive $zip): string
    {
        $container = $this->loadXml($this->readFromZip($zip, 'META-INF/container.xml'));
        $rootfile = $this->queryLocalName($container, 'rootfile')->item(0);

        throw_if($rootfile === null, new RuntimeException('EPUB container.xml has no rootfile.'));

        return $rootfile->getAttribute('full-path');
    }

    private function resolveTitle(DOMDocument $opf): string
    {
        $title = $this->queryLocalName($opf, 'title')->item(0)?->textContent;

        return filled($title) ? trim($title) : 'Untitled';
    }

    /**
     * @return array<int, array<int, string>> each entry is one chapter's paragraph texts
     */
    private function extractPages(ZipArchive $zip, DOMDocument $opf, string $opfDirectory): array
    {
        $hrefsById = [];

        foreach ($this->queryLocalName($opf, 'item') as $item) {
            $hrefsById[$item->getAttribute('id')] = $item->getAttribute('href');
        }

        $pages = [];

        foreach ($this->queryLocalName($opf, 'itemref') as $itemref) {
            $href = $hrefsById[$itemref->getAttribute('idref')] ?? null;

            if ($href === null || ! preg_match('/\.x?html?$/i', $href)) {
                continue;
            }

            $path = $opfDirectory === '.' ? $href : $opfDirectory.'/'.$href;
            $contents = $this->readFromZip($zip, $path, required: false);

            if ($contents === null) {
                continue;
            }

            array_push($pages, ...$this->chunkIntoPages($this->extractParagraphsFromHtml($contents)));
        }

        return $pages;
    }

    /**
     * @param  array<int, string>  $paragraphTexts
     * @return array<int, array<int, string>>
     */
    private function chunkIntoPages(array $paragraphTexts): array
    {
        $pages = [];
        $currentPage = [];
        $currentLength = 0;

        foreach ($paragraphTexts as $text) {
            if ($currentPage !== [] && $currentLength + mb_strlen($text) > self::TARGET_PAGE_CHARACTERS) {
                $pages[] = $currentPage;
                $currentPage = [];
                $currentLength = 0;
            }

            $currentPage[] = $text;
            $currentLength += mb_strlen($text);
        }

        if ($currentPage !== []) {
            $pages[] = $currentPage;
        }

        return $pages;
    }

    /**
     * @return array<int, string>
     */
    private function extractParagraphsFromHtml(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $paragraphs = [];

        foreach ($dom->getElementsByTagName('p') as $paragraph) {
            $text = trim(preg_replace('/\s+/u', ' ', $paragraph->textContent));

            if ($text !== '') {
                $paragraphs[] = $text;
            }
        }

        return $paragraphs;
    }

    /**
     * @return array<int, string>
     */
    private function splitIntoSentences(string $paragraph): array
    {
        // Protect single-letter initials/abbreviations (e.g. "U.S.", "J. R. R. Tolkien")
        // so the period between them isn't mistaken for a sentence boundary.
        $protected = preg_replace_callback(
            '/\b([A-Z])\.(?=\s?[A-Z])/u',
            fn (array $m) => $m[1]."\x00",
            $paragraph,
        );

        preg_match_all('/[^.!?]+(?:[.!?]+[\'"\x{2018}\x{2019}\x{201C}\x{201D}]*|$)/u', $protected, $matches);

        $sentences = array_map(
            fn (string $s) => str_replace("\x00", '.', trim($s)),
            $matches[0] ?? [],
        );

        return array_values(array_filter($sentences, fn (string $s) => $s !== ''));
    }

    private function readFromZip(ZipArchive $zip, string $path, bool $required = true): ?string
    {
        $contents = $zip->getFromName($path);

        if ($contents === false) {
            throw_if($required, new RuntimeException("Missing {$path} inside the EPUB."));

            return null;
        }

        return $contents;
    }

    private function loadXml(string $xml): DOMDocument
    {
        $dom = new DOMDocument;
        $dom->loadXML($xml);

        return $dom;
    }

    private function queryLocalName(DOMDocument $dom, string $localName): DOMNodeList
    {
        return (new DOMXPath($dom))->query("//*[local-name()=\"{$localName}\"]");
    }
}
