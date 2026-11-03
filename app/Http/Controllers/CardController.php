<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardStoreRequest;
use App\Http\Requests\CardUpdateRequest;
use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    /**
     * Display a listing of the cards.
     */
    public function index(): Response
    {
        $cards = Card::query()
            ->with(['translations.language', 'groups'])
            ->orderBy('id')
            ->get()
            ->map(fn (Card $card) => [
                'id' => $card->id,
                'translations' => $card->translations->map(fn ($translation) => [
                    'language_code' => $translation->language->code,
                    'text' => $translation->text,
                ]),
                'groups' => $card->groups->pluck('name'),
            ]);

        return Inertia::render('cards/Index', [
            'cards' => $cards,
        ]);
    }

    /**
     * Show the form for creating a new card.
     */
    public function create(): Response
    {
        return Inertia::render('cards/Create', [
            'languages' => Language::query()->orderBy('name')->get(['id', 'code', 'name']),
            'groups' => CardGroup::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created card.
     */
    public function store(CardStoreRequest $request): RedirectResponse
    {
        $card = Card::create();

        $card->syncTranslations($request->validated('translations', []));
        $card->groups()->sync($request->validated('groups', []));

        return redirect()->route('cards.index');
    }

    /**
     * Show the form for editing the given card.
     */
    public function edit(Card $card): Response
    {
        $card->load(['translations', 'groups']);

        return Inertia::render('cards/Edit', [
            'card' => [
                'id' => $card->id,
                'translations' => $card->translations->mapWithKeys(
                    fn ($translation) => [$translation->language_id => $translation->text]
                ),
                'groupIds' => $card->groups->pluck('id'),
            ],
            'languages' => Language::query()->orderBy('name')->get(['id', 'code', 'name']),
            'groups' => CardGroup::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update the given card.
     */
    public function update(CardUpdateRequest $request, Card $card): RedirectResponse
    {
        $card->syncTranslations($request->validated('translations', []));
        $card->groups()->sync($request->validated('groups', []));

        return redirect()->route('cards.index');
    }

    /**
     * Remove the given card.
     */
    public function destroy(Card $card): RedirectResponse
    {
        $card->delete();

        return redirect()->route('cards.index');
    }
}
