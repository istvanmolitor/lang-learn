<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardTypeStoreRequest;
use App\Http\Requests\CardTypeUpdateRequest;
use App\Models\CardType;
use App\Repositories\Contracts\CardTypeRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardTypeController extends Controller
{
    public function __construct(
        private readonly CardTypeRepositoryInterface $cardTypes,
    ) {}

    /**
     * Display a listing of the card types.
     */
    public function index(): Response
    {
        return Inertia::render('card-types/Index', [
            'cardTypes' => $this->cardTypes->allWithCardsCountOrderedByName()
                ->map(fn (CardType $cardType) => [
                    'id' => $cardType->id,
                    'code' => $cardType->code,
                    'name' => $cardType->name,
                    'cards_count' => $cardType->cards_count,
                ]),
        ]);
    }

    /**
     * Show the form for creating a new card type.
     */
    public function create(): Response
    {
        return Inertia::render('card-types/Create');
    }

    /**
     * Store a newly created card type.
     */
    public function store(CardTypeStoreRequest $request): RedirectResponse
    {
        $this->cardTypes->create($request->validated());

        return redirect()->route('card-types.index');
    }

    /**
     * Show the form for editing the given card type.
     */
    public function edit(CardType $cardType): Response
    {
        return Inertia::render('card-types/Edit', [
            'cardType' => $cardType->only('id', 'code', 'name'),
        ]);
    }

    /**
     * Update the given card type.
     */
    public function update(CardTypeUpdateRequest $request, CardType $cardType): RedirectResponse
    {
        $this->cardTypes->update($cardType, $request->validated());

        return redirect()->route('card-types.index');
    }

    /**
     * Remove the given card type.
     */
    public function destroy(CardType $cardType): RedirectResponse
    {
        $this->cardTypes->delete($cardType);

        return redirect()->route('card-types.index');
    }
}
