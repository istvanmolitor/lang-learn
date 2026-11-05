<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookStoreRequest;
use App\Jobs\ImportEpubBookJob;
use App\Models\Book;
use App\Repositories\Contracts\BookPageRepositoryInterface;
use App\Repositories\Contracts\BookRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\SentenceRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    public function __construct(
        private readonly BookRepositoryInterface $books,
        private readonly LanguageRepositoryInterface $languages,
        private readonly BookPageRepositoryInterface $bookPages,
        private readonly SentenceRepositoryInterface $sentences,
    ) {}

    /**
     * Display a listing of the books.
     */
    public function index(): Response
    {
        $books = $this->books->allWithLanguageOrderedByTitle()
            ->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'language' => $book->language->name,
            ]);

        return Inertia::render('books/Index', [
            'books' => $books,
            'status' => session('status'),
        ]);
    }

    /**
     * Show the form for uploading a new book (EPUB).
     */
    public function create(): Response
    {
        return Inertia::render('books/Create', [
            'languages' => $this->languages->allOrderedByName(['id', 'name']),
        ]);
    }

    /**
     * Store the uploaded EPUB and queue it for background import, since
     * parsing and saving a whole book is too slow to do within the request.
     */
    public function store(BookStoreRequest $request): RedirectResponse
    {
        $path = $request->file('file')->store('epub-imports');

        ImportEpubBookJob::dispatch($path, (int) $request->validated('language_id'));

        return redirect()->route('books.index')
            ->with('status', 'A könyv feldolgozása elindult a háttérben. Néhány percen belül megjelenik a listában.');
    }

    /**
     * Display one page of the given book so it can be read page by page.
     */
    public function show(Request $request, Book $book): Response|RedirectResponse
    {
        $this->books->loadForShow($book);

        $user = $request->user();
        $page = max((int) $request->query('page', 1), 1);

        $pages = $this->bookPages->paginateForBook($book, $page);

        if ($pages->isEmpty() && $page > $pages->lastPage()) {
            return redirect()->route('books.show', ['book' => $book, 'page' => $pages->lastPage()]);
        }

        $bookPage = $pages->first();

        $sentences = $bookPage
            ? $bookPage->paragraphs->flatMap(fn ($paragraph) => $paragraph->sentences)
            : collect();

        $cardsBySentenceId = $sentences->isNotEmpty()
            ? $this->sentences->cardsBySentenceId($sentences)
            : collect();

        return Inertia::render('books/Show', [
            'book' => [
                'id' => $book->id,
                'title' => $book->title,
                'language' => $book->language->name,
            ],
            'paragraphs' => $bookPage
                ? $bookPage->paragraphs->map(fn ($paragraph) => [
                    'id' => $paragraph->id,
                    'sentences' => $paragraph->sentences->map(fn ($sentence) => [
                        'id' => $sentence->id,
                        'text' => $sentence->text,
                        'translation' => $sentence->translations
                            ->firstWhere('language_id', $user->language_id)
                            ?->text,
                        'cards' => $cardsBySentenceId->get($sentence->id, collect())
                            ->map(fn ($card) => [
                                'id' => $card->id,
                                'translations' => $card->translations->map(fn ($translation) => [
                                    'language_code' => $translation->language->code,
                                    'text' => $translation->text,
                                ]),
                            ]),
                    ]),
                ])
                : [],
            'pagination' => [
                'currentPage' => $pages->currentPage(),
                'lastPage' => $pages->lastPage(),
            ],
        ]);
    }
}
