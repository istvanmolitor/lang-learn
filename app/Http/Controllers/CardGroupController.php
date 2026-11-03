<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardGroupStoreRequest;
use App\Http\Requests\CardGroupUpdateRequest;
use App\Models\CardGroup;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CardGroupController extends Controller
{
    /**
     * Display a listing of the card groups.
     */
    public function index(): Response
    {
        return Inertia::render('card-groups/Index', [
            'groups' => CardGroup::query()
                ->withCount('cards')
                ->orderBy('name')
                ->get()
                ->map(fn (CardGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'cards_count' => $group->cards_count,
                ]),
        ]);
    }

    /**
     * Show the form for creating a new card group.
     */
    public function create(): Response
    {
        return Inertia::render('card-groups/Create');
    }

    /**
     * Store a newly created card group.
     */
    public function store(CardGroupStoreRequest $request): RedirectResponse
    {
        CardGroup::create($request->validated());

        return redirect()->route('card-groups.index');
    }

    /**
     * Show the form for editing the given card group.
     */
    public function edit(CardGroup $cardGroup): Response
    {
        return Inertia::render('card-groups/Edit', [
            'group' => $cardGroup->only('id', 'name'),
        ]);
    }

    /**
     * Update the given card group.
     */
    public function update(CardGroupUpdateRequest $request, CardGroup $cardGroup): RedirectResponse
    {
        $cardGroup->update($request->validated());

        return redirect()->route('card-groups.index');
    }

    /**
     * Remove the given card group.
     */
    public function destroy(CardGroup $cardGroup): RedirectResponse
    {
        $cardGroup->delete();

        return redirect()->route('card-groups.index');
    }
}
