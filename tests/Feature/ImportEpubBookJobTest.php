<?php

use App\Jobs\ImportEpubBookJob;
use App\Models\Book;
use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Services\EpubBookImporter;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

function writeTestEpubIntoLocalStorage(string $title, string $bodyHtml): string
{
    $zip = new ZipArchive;
    $tempPath = tempnam(sys_get_temp_dir(), 'import-epub-book-job-test-').'.epub';

    $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $zip->addFromString('mimetype', 'application/epub+zip');
    $zip->addFromString('META-INF/container.xml', <<<'XML'
        <?xml version="1.0"?>
        <container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
        <rootfiles>
        <rootfile full-path="OEBPS/package.opf" media-type="application/oebps-package+xml"/>
        </rootfiles>
        </container>
        XML);

    $zip->addFromString('OEBPS/package.opf', <<<XML
        <?xml version="1.0"?>
        <package xmlns="http://www.idpf.org/2007/opf" unique-identifier="pub-id" version="3.0">
        <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
        <dc:title>{$title}</dc:title>
        </metadata>
        <manifest>
        <item href="xhtml/chapter1.xhtml" id="chapter1" media-type="application/xhtml+xml"/>
        </manifest>
        <spine>
        <itemref idref="chapter1"/>
        </spine>
        </package>
        XML);

    $zip->addFromString('OEBPS/xhtml/chapter1.xhtml', <<<XML
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head><meta content="text/html; charset=utf-8" http-equiv="Content-Type"/></head>
        <body>
        {$bodyHtml}
        </body>
        </html>
        XML);

    $zip->close();

    $storedPath = 'epub-imports/'.basename($tempPath);
    Storage::disk('local')->put($storedPath, file_get_contents($tempPath));
    unlink($tempPath);

    return $storedPath;
}

test('it imports the stored epub into a book and deletes the temporary file', function () {
    Storage::fake('local');

    $english = Language::factory()->create(['code' => 'en']);
    $path = writeTestEpubIntoLocalStorage('Queued Book', '<p>Hello world. Goodbye world.</p>');

    (new ImportEpubBookJob($path, $english->id))->handle(
        app(EpubBookImporter::class),
        app(LanguageRepositoryInterface::class),
    );

    $book = Book::query()->where('title', 'Queued Book')->firstOrFail();
    expect($book->language_id)->toBe($english->id);
    expect($book->pages()->count())->toBe(1);

    $sentences = $book->paragraphs()->first()->sentences()->pluck('text')->all();
    expect($sentences)->toBe(['Hello world.', 'Goodbye world.']);

    Storage::disk('local')->assertMissing($path);
});

test('it deletes the temporary file even when the import fails', function () {
    Storage::fake('local');

    $english = Language::factory()->create(['code' => 'en']);
    $path = 'epub-imports/not-a-real-epub.epub';
    Storage::disk('local')->put($path, 'not an epub');

    expect(fn () => (new ImportEpubBookJob($path, $english->id))->handle(
        app(EpubBookImporter::class),
        app(LanguageRepositoryInterface::class),
    ))->toThrow(RuntimeException::class);

    Storage::disk('local')->assertMissing($path);
});
