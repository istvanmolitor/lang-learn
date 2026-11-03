<?php

namespace App\Http\Controllers;

use App\Enums\CardDifficulty;
use App\Http\Requests\LearnAnswerRequest;
use App\Models\Card;
use App\Models\CardGroup;
use App\Models\CardReview;
use App\Models\User;
use App\Services\SpacedRepetitionScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    /**
     * Show the next due flashcard for the given card group.
     */
    public function show(CardGroup $cardGroup): Response
    {
        $user = request()->user();
        $user->loadMissing(['selectedLanguage', 'nativeLanguage']);

        $card = $this->nextCardFor($cardGroup, $user);

        return Inertia::render('learn/Show', [
            'group' => $cardGroup->only('id', 'name'),
            'card' => $card ? $this->cardPayload($card, $user) : null,
        ]);
    }

    /**
     * Record how difficult the given card's answer was, and move on to the next one.
     */
    public function store(LearnAnswerRequest $request, CardGroup $cardGroup): RedirectResponse
    {
        $user = $request->user();

        $card = $cardGroup->cards()->whereKey($request->validated('card_id'))->firstOrFail();
        $difficulty = CardDifficulty::from($request->validated('difficulty'));

        $previous = CardReview::query()
            ->where('user_id', $user->id)
            ->where('card_id', $card->id)
            ->latest('id')
            ->first();

        $schedule = (new SpacedRepetitionScheduler)->schedule($previous, $difficulty);

        CardReview::create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'difficulty' => $difficulty,
            ...$schedule,
        ]);

        return redirect()->route('card-groups.learn', $cardGroup);
    }

    /**
     * Pick the card that is due soonest for the user, giving unreviewed cards priority.
     */
    private function nextCardFor(CardGroup $cardGroup, User $user): ?Card
    {
        $cards = $cardGroup->cards()
            ->with(['translations.language', 'reviews' => fn ($query) => $query->where('user_id', $user->id)->latest('id')->limit(1)])
            ->get();

        return $cards->sortBy(function (Card $card) {
            $latestReview = $card->reviews->first();

            return $latestReview === null ? Carbon::createFromTimestamp(0) : $latestReview->due_at;
        })->first();
    }

    /**
     * Build the front (language being learned) and back (native language) texts for a card.
     *
     * @return array{id: int, front: string|null, frontLanguage: string|null, back: string|null, backLanguage: string|null}
     */
    private function cardPayload(Card $card, User $user): array
    {
        $textByLanguageId = $card->translations->pluck('text', 'language_id');

        return [
            'id' => $card->id,
            'front' => $textByLanguageId->get($user->selected_language_id),
            'frontLanguage' => $user->selectedLanguage?->name,
            'back' => $textByLanguageId->get($user->language_id),
            'backLanguage' => $user->nativeLanguage?->name,
        ];
    }
}
