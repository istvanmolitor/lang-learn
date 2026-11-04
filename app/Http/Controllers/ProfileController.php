<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Language;
use App\Repositories\Contracts\LanguageRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly LanguageRepositoryInterface $languages,
    ) {}

    /**
     * Show the user's profile settings.
     */
    public function edit(): Response
    {
        $user = request()->user();

        return Inertia::render('Profile', [
            'languages' => $this->languages->allOrderedByName(['id', 'code', 'name', 'native_name']),
            'nativeLanguageId' => $user->language_id,
            'userLanguages' => $this->users->knownLanguagesFor($user, ['languages.id'])
                ->map(fn (Language $language) => [
                    'language_id' => $language->id,
                    'level' => $language->pivot->level,
                ]),
        ]);
    }

    /**
     * Update the user's profile settings.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $nativeLanguageId = $request->validated('native_language_id');

        $this->users->updateNativeLanguage($user, $nativeLanguageId);
        $this->users->syncLanguages($user, $request->validated('languages'));

        $learnableLanguageIds = $this->users->knownLanguageIdsFor($user)->reject(
            fn (int $languageId) => $languageId === $nativeLanguageId
        );

        if ($user->selected_language_id && ! $learnableLanguageIds->contains($user->selected_language_id)) {
            $this->users->updateSelectedLanguage($user, null);
        }

        return redirect()->route('profile.edit');
    }
}
