<?php

use App\Models\Card;
use App\Models\Language;
use App\Models\Sentence;
use App\Models\User;
use App\Models\Word;

test('guests cannot view the words page', function () {
    $this->get(route('words.index'))->assertRedirect(route('login'));
});

test('the words index is paginated', function () {
    $user = User::factory()->create();
    Word::factory()->count(25)->create();

    $response = $this->actingAs($user)->get(route('words.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('words/Index')
        ->has('words.data', 20)
        ->where('words.total', 25)
    );
});

test('the words index can be searched by word text', function () {
    $user = User::factory()->create();
    $language = Language::factory()->create();

    $dog = Word::factory()->create(['word' => 'dog', 'language_id' => $language->id]);
    Word::factory()->create(['word' => 'cat', 'language_id' => $language->id]);

    $response = $this->actingAs($user)->get(route('words.index', ['search' => 'dog']));

    $response->assertInertia(fn ($page) => $page
        ->component('words/Index')
        ->has('words.data', 1)
        ->where('words.data.0.id', $dog->id)
    );
});

test('the words index orders words by sentence count descending', function () {
    $user = User::factory()->create();
    $language = Language::factory()->create();

    $rare = Word::factory()->create(['word' => 'rare', 'language_id' => $language->id]);
    $common = Word::factory()->create(['word' => 'common', 'language_id' => $language->id]);

    $rare->sentences()->attach(Sentence::factory()->create());
    $common->sentences()->attach(Sentence::factory()->count(3)->create());

    $response = $this->actingAs($user)->get(route('words.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('words/Index')
        ->where('words.data.0.id', $common->id)
        ->where('words.data.0.sentences_count', 3)
        ->where('words.data.1.id', $rare->id)
        ->where('words.data.1.sentences_count', 1)
    );
});

test('the words index includes the number of cards each word appears in', function () {
    $user = User::factory()->create();
    $language = Language::factory()->create();
    $word = Word::factory()->create(['word' => 'dog', 'language_id' => $language->id]);

    $word->cards()->attach(Card::factory()->count(2)->create());

    $response = $this->actingAs($user)->get(route('words.index'));

    $response->assertInertia(fn ($page) => $page
        ->component('words/Index')
        ->where('words.data.0.id', $word->id)
        ->where('words.data.0.cards_count', 2)
    );
});
