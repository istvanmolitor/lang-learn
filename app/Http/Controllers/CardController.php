<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardStoreRequest;
use App\Http\Requests\CardUpdateRequest;
use App\Models\Card;
use App\Repositories\Contracts\CardGroupRepositoryInterface;
use App\Repositories\Contracts\CardRepositoryInterface;
use App\Repositories\Contracts\CardTypeRepositoryInterface;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CardController extends Controller
{
    public function __construct(
        private readonly CardRepositoryInterface $cards,
        private readonly CardGroupRepositoryInterface $cardGroups,
        private readonly LanguageRepositoryInterface $languages,
        private readonly CardTypeRepositoryInterface $cardTypes,
    ) {}

    /**
     * Display a listing of the cards.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $cards = $this->cards->paginateSearch($search)
            ->through(fn (Card $card) => [
                'id' => $card->id,
                'translations' => $card->translations->map(fn ($translation) => [
                    'language_code' => $translation->language->code,
                    'text' => $translation->text,
                ]),
                'groups' => $card->groups->pluck('name'),
                'type' => $card->cardType?->name,
            ]);

        return Inertia::render('cards/Index', [
            'cards' => $cards,
            'search' => $search,
        ]);
    }

    /**
     * Show the form for creating a new card.
     */
    public function create(): Response
    {
        return Inertia::render('cards/Create', [
            'languages' => $this->languages->allOrderedByName(['id', 'code', 'name']),
            'groups' => $this->cardGroups->allOrderedByName(['id', 'name']),
            'cardTypes' => $this->cardTypes->allOrderedByName(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created card.
     */
    public function store(CardStoreRequest $request): RedirectResponse
    {
        $card = $this->cards->create([
            'card_type_id' => $request->validated('card_type_id'),
        ]);

        $this->cards->syncTranslations($card, $request->validated('translations', []));
        $this->cards->syncGroups($card, $request->validated('groups', []));

        return redirect()->route('cards.index');
    }

    /**
     * Show the form for editing the given card.
     */
    public function edit(Card $card): Response
    {
        $this->cards->loadForEdit($card);

        return Inertia::render('cards/Edit', [
            'card' => [
                'id' => $card->id,
                'translations' => $card->translations->mapWithKeys(
                    fn ($translation) => [$translation->language_id => $translation->text]
                ),
                'groupIds' => $card->groups->pluck('id'),
                'cardTypeId' => $card->card_type_id,
            ],
            'languages' => $this->languages->allOrderedByName(['id', 'code', 'name']),
            'groups' => $this->cardGroups->allOrderedByName(['id', 'name']),
            'cardTypes' => $this->cardTypes->allOrderedByName(['id', 'name']),
        ]);
    }

    /**
     * Update the given card.
     */
    public function update(CardUpdateRequest $request, Card $card): RedirectResponse
    {
        $this->cards->update($card, [
            'card_type_id' => $request->validated('card_type_id'),
        ]);
        $this->cards->syncTranslations($card, $request->validated('translations', []));
        $this->cards->syncGroups($card, $request->validated('groups', []));

        return redirect()->route('cards.index');
    }

    /**
     * Remove the given card.
     */
    public function destroy(Card $card): RedirectResponse
    {
        $this->cards->delete($card);

        return redirect()->route('cards.index');
    }
}
