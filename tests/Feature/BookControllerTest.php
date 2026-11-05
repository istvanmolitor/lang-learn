<?php

use App\Jobs\ImportEpubBookJob;
use App\Models\Book;
use App\Models\BookPage;
use App\Models\Card;
use App\Models\CardTranslation;
use App\Models\Language;
use App\Models\Paragraph;
use App\Models\User;
use App\Models\Word;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

function writeTestEpubFile(string $title, string $bodyHtml): string
{
    $path = tempnam(sys_get_temp_dir(), 'book-controller-test-').'.epub';

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

    return $path;
}

test('guests cannot upload a book', function () {
    $this->get(route('books.create'))->assertRedirect(route('login'));
});

test('uploading an epub stores it and queues the import instead of running it inline', function () {
    Queue::fake();
    Storage::fake('local');

    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);

    $path = writeTestEpubFile('Uploaded Book', '<p>Hello world. Goodbye world.</p>');
    $file = new UploadedFile($path, 'book.epub', 'application/epub+zip', null, true);

    $response = $this->actingAs($user)->post(route('books.store'), [
        'file' => $file,
        'language_id' => $english->id,
    ]);

    $response->assertRedirect(route('books.index'));
    expect(Book::query()->count())->toBe(0);

    Queue::assertPushed(ImportEpubBookJob::class, function (ImportEpubBookJob $job) use ($english) {
        Storage::disk('local')->assertExists($job->path);

        return $job->languageId === $english->id;
    });
});

test('uploading a non-epub file is rejected', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);

    $file = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

    $response = $this->actingAs($user)->post(route('books.store'), [
        'file' => $file,
        'language_id' => $english->id,
    ]);

    $response->assertInvalid(['file']);
});

test('the show page displays one page of paragraphs at a time', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $firstPage = BookPage::factory()->for($book)->create(['position' => 0]);
    $firstParagraph = Paragraph::factory()->for($firstPage, 'page')->create(['position' => 0]);
    $firstParagraph->sentences()->create(['position' => 0, 'text' => 'First page sentence.']);

    $secondPage = BookPage::factory()->for($book)->create(['position' => 1]);
    $secondParagraph = Paragraph::factory()->for($secondPage, 'page')->create(['position' => 0]);
    $secondParagraph->sentences()->create(['position' => 0, 'text' => 'Second page sentence.']);

    $response = $this->actingAs($user)->get(route('books.show', $book));

    $response->assertInertia(fn ($page) => $page
        ->component('books/Show')
        ->where('paragraphs.0.id', $firstParagraph->id)
        ->where('paragraphs.0.sentences.0.text', 'First page sentence.')
        ->where('pagination.currentPage', 1)
        ->where('pagination.lastPage', 2)
    );

    $response = $this->actingAs($user)->get(route('books.show', ['book' => $book, 'page' => 2]));

    $response->assertInertia(fn ($page) => $page
        ->component('books/Show')
        ->where('paragraphs.0.id', $secondParagraph->id)
        ->where('paragraphs.0.sentences.0.text', 'Second page sentence.')
        ->where('pagination.currentPage', 2)
        ->where('pagination.lastPage', 2)
    );
});

test('the show page lists the cards that share a word with the sentence', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create();
    $book = Book::factory()->create(['language_id' => $english->id]);
    $page = BookPage::factory()->for($book)->create(['position' => 0]);
    $paragraph = Paragraph::factory()->for($page, 'page')->create(['position' => 0]);
    $sentence = $paragraph->sentences()->create(['position' => 0, 'text' => 'I have a dog.']);

    $word = Word::factory()->create(['language_id' => $english->id, 'word' => 'dog']);
    $sentence->words()->attach($word);

    $card = Card::factory()->create();
    CardTranslation::factory()->create(['card_id' => $card->id, 'language_id' => $english->id, 'text' => 'dog']);
    $card->words()->attach($word);

    $response = $this->actingAs($user)->get(route('books.show', $book));

    $response->assertInertia(fn ($page) => $page
        ->component('books/Show')
        ->where('paragraphs.0.sentences.0.cards.0.id', $card->id)
        ->where('paragraphs.0.sentences.0.cards.0.translations.0.text', 'dog')
    );
});

test('requesting a page beyond the last book page redirects to the last page', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();
    BookPage::factory()->for($book)->create(['position' => 0]);

    $response = $this->actingAs($user)->get(route('books.show', ['book' => $book, 'page' => 5]));

    $response->assertRedirect(route('books.show', ['book' => $book, 'page' => 1]));
});

test('a book with no pages shows an empty state instead of erroring', function () {
    $user = User::factory()->create();
    $book = Book::factory()->create();

    $response = $this->actingAs($user)->get(route('books.show', $book));

    $response->assertInertia(fn ($page) => $page
        ->component('books/Show')
        ->where('paragraphs', [])
        ->where('pagination.currentPage', 1)
        ->where('pagination.lastPage', 1)
    );
});
