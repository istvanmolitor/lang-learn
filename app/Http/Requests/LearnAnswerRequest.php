<?php

namespace App\Http\Requests;

use App\Enums\CardDifficulty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LearnAnswerRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'card_id' => ['required', 'integer', 'exists:cards,id'],
            'difficulty' => ['required', Rule::enum(CardDifficulty::class)],
        ];
    }
}
