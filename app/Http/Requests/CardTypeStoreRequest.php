<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardTypeStoreRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'unique:card_types,code'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
