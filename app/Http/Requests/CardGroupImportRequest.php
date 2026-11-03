<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardGroupImportRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:csv,txt'],
        ];
    }
}
