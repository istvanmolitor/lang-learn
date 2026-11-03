<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardUpdateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'translations' => ['array'],
            'translations.*.language_id' => ['required', 'integer', 'distinct', 'exists:languages,id'],
            'translations.*.text' => ['nullable', 'string', 'max:1000'],
            'groups' => ['array'],
            'groups.*' => ['integer', 'exists:card_groups,id'],
        ];
    }
}
