<?php

use App\Enums\LanguageLevel;
use App\Models\Language;
use App\Models\User;

test('guests cannot view the profile page', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
});

test('users can view their profile page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertOk();
});

test('users can set their native language and known languages', function () {
    $user = User::factory()->create();
    $hungarian = Language::factory()->create(['code' => 'hu']);
    $english = Language::factory()->create(['code' => 'en']);

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'native_language_id' => $hungarian->id,
        'languages' => [
            ['language_id' => $english->id, 'level' => LanguageLevel::B2->value],
        ],
    ]);

    $response->assertRedirect(route('profile.edit'));

    expect($user->refresh()->language_id)->toBe($hungarian->id);

    $knownLanguage = $user->languages()->first();
    expect($knownLanguage->id)->toBe($english->id);
    expect($knownLanguage->pivot->level)->toBe(LanguageLevel::B2);
});

test('known languages must reference existing languages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('profile.update'), [
        'languages' => [
            ['language_id' => 9999, 'level' => LanguageLevel::A1->value],
        ],
    ]);

    $response->assertInvalid(['languages.0.language_id']);
});

test('removing the selected language from known languages clears the selection', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $user->languages()->attach($english, ['level' => LanguageLevel::B2]);
    $user->update(['selected_language_id' => $english->id]);

    $this->actingAs($user)->put(route('profile.update'), [
        'languages' => [],
    ]);

    expect($user->refresh()->selected_language_id)->toBeNull();
});

test('setting the native language to the selected language clears the selection', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $user->languages()->attach($english, ['level' => LanguageLevel::B2]);
    $user->update(['selected_language_id' => $english->id]);

    $this->actingAs($user)->put(route('profile.update'), [
        'native_language_id' => $english->id,
        'languages' => [
            ['language_id' => $english->id, 'level' => LanguageLevel::B2->value],
        ],
    ]);

    expect($user->refresh()->selected_language_id)->toBeNull();
});
