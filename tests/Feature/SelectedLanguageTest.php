<?php

use App\Enums\LanguageLevel;
use App\Models\Language;
use App\Models\User;

test('guests cannot update the selected language', function () {
    $this->put(route('selected-language.update'), ['language_id' => 1])
        ->assertRedirect(route('login'));
});

test('users can select a language they know', function () {
    $user = User::factory()->create();
    $english = Language::factory()->create(['code' => 'en']);
    $user->languages()->attach($english, ['level' => LanguageLevel::B2]);

    $response = $this->actingAs($user)->put(route('selected-language.update'), [
        'language_id' => $english->id,
    ]);

    $response->assertRedirect();
    expect($user->refresh()->selected_language_id)->toBe($english->id);
});

test('users cannot select their native language', function () {
    $user = User::factory()->create();
    $hungarian = Language::factory()->create(['code' => 'hu']);
    $user->update(['language_id' => $hungarian->id]);
    $user->languages()->attach($hungarian, ['level' => LanguageLevel::C2]);

    $response = $this->actingAs($user)->put(route('selected-language.update'), [
        'language_id' => $hungarian->id,
    ]);

    $response->assertInvalid(['language_id']);
});

test('users cannot select a language they do not know', function () {
    $user = User::factory()->create();
    $german = Language::factory()->create(['code' => 'de']);

    $response = $this->actingAs($user)->put(route('selected-language.update'), [
        'language_id' => $german->id,
    ]);

    $response->assertInvalid(['language_id']);
});
