<?php

use App\Models\Book;
use App\Models\Language;
use App\Services\EpubBookImporter;
use ZipArchive;

function writeTestEpub(string $title, array $chapterHtmlBodies): string
{
    $path = tempnam(sys_get_temp_dir(), 'epub-book-importer-test-').'.epub';

    $zip = new ZipArchive;
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $zip->addFromString('mimetype', 'application/epub+zip');
    $zip->addFromString('META-INF/container.xml', <<<'XML'
        <?xml version="1.0"?>
        <container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
        <rootfiles>
        <rootfile full-path="OEBPS/package.opf" media-type="application/oebps-package+xml"/>
        </rootfiles>
        </container>
        XML);

    $manifestItems = '';
    $spineItems = '';

    foreach (array_keys($chapterHtmlBodies) as $index) {
        $id = "chapter{$index}";
        $manifestItems .= "<item href=\"xhtml/{$id}.xhtml\" id=\"{$id}\" media-type=\"application/xhtml+xml\"/>\n";
        $spineItems .= "<itemref idref=\"{$id}\"/>\n";
    }

    $zip->addFromString('OEBPS/package.opf', <<<XML
        <?xml version="1.0"?>
        <package xmlns="http://www.idpf.org/2007/opf" unique-identifier="pub-id" version="3.0">
        <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:title>{$title}</dc:title>
        </metadata>
        <manifest>
        {$manifestItems}
        </manifest>
        <spine>
        {$spineItems}
        </spine>
        </package>
        XML);

    foreach ($chapterHtmlBodies as $index => $body) {
        $zip->addFromString("OEBPS/xhtml/chapter{$index}.xhtml", <<<XML
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"/></head>
            <body>
            {$body}
            </body>
            </html>
            XML);
    }

    $zip->close();

    return $path;
}

test('it imports an epub into a book with one page per chapter and trimmed sentences', function () {
    $english = Language::factory()->create(['code' => 'en']);

    $path = writeTestEpub('My Test Book', [
        1 => '<p> Hello there.  How are you? </p><p>I am fine! Thanks for asking.</p>',
    ]);

    $book = app(EpubBookImporter::class)->import($path, 'en');

    expect($book->title)->toBe('My Test Book');
    expect($book->language_id)->toBe($english->id);

    $book->load('pages.paragraphs.sentences');
    expect($book->pages)->toHaveCount(1);

    $paragraphs = $book->pages[0]->paragraphs;
    expect($paragraphs)->toHaveCount(2);

    expect($paragraphs[0]->sentences->pluck('text')->all())->toBe(['Hello there.', 'How are you?']);
    expect($paragraphs[1]->sentences->pluck('text')->all())->toBe(['I am fine!', 'Thanks for asking.']);
});

test('it skips empty paragraphs and preserves chapter order as separate pages', function () {
    Language::factory()->create(['code' => 'en']);

    $path = writeTestEpub('Ordered Book', [
        1 => '<p>First chapter sentence.</p><p>   </p>',
        2 => '<p>Second chapter sentence.</p>',
    ]);

    $book = app(EpubBookImporter::class)->import($path, 'en');
    $book->load('pages.paragraphs.sentences');

    expect($book->pages)->toHaveCount(2);
    expect($book->pages[0]->paragraphs)->toHaveCount(1);
    expect($book->pages[0]->paragraphs[0]->sentences->pluck('text')->all())->toBe(['First chapter sentence.']);
    expect($book->pages[1]->paragraphs)->toHaveCount(1);
    expect($book->pages[1]->paragraphs[0]->sentences->pluck('text')->all())->toBe(['Second chapter sentence.']);
});

test('it splits a long chapter into multiple page-sized chunks without splitting a paragraph', function () {
    Language::factory()->create(['code' => 'en']);

    // 10 paragraphs of 150 chars each (1500 chars total) comfortably exceed
    // the importer's ~1200 character page target, so this chapter must be
    // split into more than one page.
    $paragraphs = [];

    for ($i = 1; $i <= 10; $i++) {
        $paragraphs[] = "Paragraph number {$i}. ".str_repeat('x', 150 - mb_strlen("Paragraph number {$i}. "));
    }

    $path = writeTestEpub('Long Chapter Book', [
        1 => implode('', array_map(fn (string $p) => "<p>{$p}</p>", $paragraphs)),
    ]);

    $book = app(EpubBookImporter::class)->import($path, 'en');
    $book->load('pages.paragraphs');

    expect($book->pages->count())->toBeGreaterThan(1);
    expect($book->pages->sum(fn ($page) => $page->paragraphs->count()))->toBe(10);
});

test('it does not reimport a book that already exists for the same language', function () {
    Language::factory()->create(['code' => 'en']);

    $path = writeTestEpub('Duplicate Book', [
        1 => '<p>Only sentence.</p>',
    ]);

    $importer = app(EpubBookImporter::class);
    $importer->import($path, 'en');
    $importer->import($path, 'en');

    expect(Book::query()->where('title', 'Duplicate Book')->count())->toBe(1);
});
