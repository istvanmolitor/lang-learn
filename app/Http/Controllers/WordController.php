<?php

namespace App\Http\Controllers;

use App\Models\Word;
use App\Repositories\Contracts\WordRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WordController extends Controller
{
    public function __construct(
        private readonly WordRepositoryInterface $words,
    ) {}

    /**
     * Display a listing of the words.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $words = $this->words->paginateSearch($search)
            ->through(fn (Word $word) => [
                'id' => $word->id,
                'word' => $word->word,
                'sentences_count' => $word->sentences_count,
                'cards_count' => $word->cards_count,
            ]);

        return Inertia::render('words/Index', [
            'words' => $words,
            'search' => $search,
        ]);
    }
}
