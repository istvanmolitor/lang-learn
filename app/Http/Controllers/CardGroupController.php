<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardGroupImportRequest;
use App\Http\Requests\CardGroupStoreRequest;
use App\Http\Requests\CardGroupUpdateRequest;
use App\Models\Card;
use App\Models\CardGroup;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CardGroupController extends Controller
{
    /**
     * Display a listing of the card groups.
     */
    public function index(): Response
    {
        $user = request()->user();

        return Inertia::render('card-groups/Index', [
            'groups' => CardGroup::query()
                ->withCount('cards')
                ->orderBy('name')
                ->get()
                ->map(fn (CardGroup $group) => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'cards_count' => $group->cards_count,
                    'progress' => $group->progressPercentageFor($user),
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

    /**
     * Export the given card group's cards as a CSV file.
     */
    public function export(CardGroup $cardGroup): StreamedResponse
    {
        $languages = Language::query()->orderBy('code')->get(['id', 'code']);

        $cards = $cardGroup->cards()->with('translations')->orderBy('id')->get();

        $filename = Str::slug($cardGroup->name).'.csv';

        return response()->streamDownload(function () use ($languages, $cards) {
            $handle = fopen('php://output', 'w');

            throw_if($handle === false, new \RuntimeException('Unable to open output stream.'));

            fputcsv($handle, $languages->pluck('code')->all());

            foreach ($cards as $card) {
                $textByLanguageId = $card->translations->pluck('text', 'language_id');

                fputcsv($handle, $languages->map(
                    fn (Language $language) => $textByLanguageId->get($language->id, '')
                )->all());
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Import cards into the given card group from an uploaded CSV file.
     */
    public function import(CardGroupImportRequest $request, CardGroup $cardGroup): RedirectResponse
    {
        $handle = fopen($request->file('file')->getRealPath(), 'r');

        throw_if($handle === false, new \RuntimeException('Unable to open uploaded file.'));

        $header = fgetcsv($handle) ?: [];

        $languageIdsByColumn = collect($header)
            ->map(fn (?string $code) => Language::query()->where('code', trim((string) $code))->value('id'))
            ->all();

        DB::transaction(function () use ($handle, $languageIdsByColumn, $cardGroup) {
            while (($row = fgetcsv($handle)) !== false) {
                $translations = [];

                foreach ($languageIdsByColumn as $column => $languageId) {
                    if ($languageId !== null) {
                        $translations[] = [
                            'language_id' => $languageId,
                            'text' => $row[$column] ?? null,
                        ];
                    }
                }

                $card = Card::create();
                $card->syncTranslations($translations);
                $cardGroup->cards()->attach($card);
            }
        });

        fclose($handle);

        return redirect()->route('card-groups.edit', $cardGroup);
    }
}
