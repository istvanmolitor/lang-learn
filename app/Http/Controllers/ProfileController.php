<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings.
     */
    public function edit(): Response
    {
        $user = request()->user();

        return Inertia::render('Profile', [
            'languages' => Language::query()->orderBy('name')->get(['id', 'code', 'name', 'native_name']),
            'nativeLanguageId' => $user->language_id,
            'userLanguages' => $user->languages()->get(['languages.id'])
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

        $user->update(['language_id' => $nativeLanguageId]);

        $user->languages()->sync(
            collect($request->validated('languages'))
                ->mapWithKeys(fn (array $row) => [$row['language_id'] => ['level' => $row['level']]])
        );

        $learnableLanguageIds = $user->languages()->pluck('languages.id')->reject(
            fn (int $languageId) => $languageId === $nativeLanguageId
        );

        if ($user->selected_language_id && ! $learnableLanguageIds->contains($user->selected_language_id)) {
            $user->update(['selected_language_id' => null]);
        }

        return redirect()->route('profile.edit');
    }
}
