<?php

namespace App\Http\Controllers;

use App\Enums\CardDifficulty;
use App\Http\Requests\LearnAnswerRequest;
use App\Models\Card;
use App\Models\CardGroup;
use App\Models\User;
use App\Repositories\Contracts\CardGroupRepositoryInterface;
use App\Repositories\Contracts\CardReviewRepositoryInterface;
use App\Repositories\Contracts\SentenceRepositoryInterface;
use App\Services\SpacedRepetitionScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class LearnController extends Controller
{
    public function __construct(
        private readonly CardGroupRepositoryInterface $cardGroups,
        private readonly CardReviewRepositoryInterface $cardReviews,
        private readonly SentenceRepositoryInterface $sentences,
    ) {}

    /**
     * Show the next due flashcard for the given card group.
     */
    public function show(CardGroup $cardGroup): Response
    {
        $user = request()->user();
        $user->loadMissing(['selectedLanguage', 'nativeLanguage']);

        $cards = $this->cardGroups->cardsWithLatestReviewFor($cardGroup, $user);
        $card = $this->nextCardFrom($cards);

        return Inertia::render('learn/Show', [
            'group' => $cardGroup->only('id', 'name'),
            'card' => $card ? $this->cardPayload($card, $user) : null,
            'progress' => CardGroup::progressPercentage($cards),
        ]);
    }

    /**
     * Record how difficult the given card's answer was, and move on to the next one.
     */
    public function store(LearnAnswerRequest $request, CardGroup $cardGroup): RedirectResponse
    {
        $user = $request->user();

        $card = $this->cardGroups->findCardInGroupOrFail($cardGroup, (int) $request->validated('card_id'));
        $difficulty = CardDifficulty::from($request->validated('difficulty'));

        $previous = $this->cardReviews->latestForUserAndCard($user->id, $card->id);

        $schedule = (new SpacedRepetitionScheduler)->schedule($previous, $difficulty);

        $this->cardReviews->create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'difficulty' => $difficulty,
            ...$schedule,
        ]);

        return redirect()->route('card-groups.learn', $cardGroup);
    }

    /**
     * Pick the card that is due soonest for the user, giving unreviewed cards priority.
     *
     * @param  Collection<int, Card>  $cards
     */
    private function nextCardFrom(Collection $cards): ?Card
    {
        return $cards->sortBy(function (Card $card) {
            $latestReview = $card->reviews->first();

            return $latestReview === null ? Carbon::createFromTimestamp(0) : $latestReview->due_at;
        })->first();
    }

    /**
     * Build the front (language being learned) and back (native language) texts for a card.
     *
     * @return array{id: int, front: string|null, frontLanguage: string|null, back: string|null, backLanguage: string|null, examples: array<int, string>}
     */
    private function cardPayload(Card $card, User $user): array
    {
        $textByLanguageId = $card->translations->pluck('text', 'language_id');

        $examples = $user->selected_language_id
            ? $this->sentences->examplesForCard($card, $user->selected_language_id)
            : collect();

        return [
            'id' => $card->id,
            'front' => $textByLanguageId->get($user->selected_language_id),
            'frontLanguage' => $user->selectedLanguage?->name,
            'back' => $textByLanguageId->get($user->language_id),
            'backLanguage' => $user->nativeLanguage?->name,
            'examples' => $examples->pluck('text')->values()->all(),
        ];
    }
}
