<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelectedLanguageUpdateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $learnableLanguageIds = $this->user()->languages()
            ->when(
                $this->user()->language_id,
                fn ($query, $languageId) => $query->where('languages.id', '!=', $languageId)
            )
            ->pluck('languages.id');

        return [
            'language_id' => ['required', 'integer', Rule::in($learnableLanguageIds)],
        ];
    }
}
