<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class BookStoreRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'bail',
                'required',
                'file',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($value instanceof UploadedFile && strtolower($value->getClientOriginalExtension()) !== 'epub') {
                        $fail('A fájlnak .epub kiterjesztésűnek kell lennie.');
                    }
                },
            ],
            'language_id' => ['required', 'integer', 'exists:languages,id'],
        ];
    }
}
