<?php

use App\Services\CardTextTypeExtractor;

test('it strips a single part-of-speech tag and returns its code', function () {
    $result = (new CardTextTypeExtractor)->extract('ability (n.)');

    expect($result)->toBe(['text' => 'ability', 'code' => 'n']);
});

test('it strips a multi part-of-speech tag and returns the first code', function () {
    $result = (new CardTextTypeExtractor)->extract('after (conj., adv.)');

    expect($result)->toBe(['text' => 'after', 'code' => 'conj']);
});

test('it only strips the part-of-speech group and leaves a disambiguation gloss', function () {
    $result = (new CardTextTypeExtractor)->extract('bear (animal) (n.)');

    expect($result)->toBe(['text' => 'bear (animal)', 'code' => 'n']);
});

test('it leaves text unchanged when the parenthesised group is not a known part of speech', function () {
    $result = (new CardTextTypeExtractor)->extract('billion (number)');

    expect($result)->toBe(['text' => 'billion (number)', 'code' => null]);
});

test('it leaves plain text without parentheses unchanged', function () {
    $result = (new CardTextTypeExtractor)->extract('dog');

    expect($result)->toBe(['text' => 'dog', 'code' => null]);
});
