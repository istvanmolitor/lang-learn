<?php

namespace App\Http\Requests;

use App\Enums\LanguageLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'native_language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'languages' => ['array'],
            'languages.*.language_id' => ['required', 'integer', 'distinct', 'exists:languages,id'],
            'languages.*.level' => ['required', Rule::enum(LanguageLevel::class)],
        ];
    }
}
