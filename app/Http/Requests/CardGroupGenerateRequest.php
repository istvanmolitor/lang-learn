<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardGroupGenerateRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'string', 'max:255'],
            'count' => ['required', 'integer', 'min:1', 'max:30'],
        ];
    }
}
