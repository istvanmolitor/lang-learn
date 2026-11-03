<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectedLanguageUpdateRequest;
use Illuminate\Http\RedirectResponse;

class SelectedLanguageController extends Controller
{
    /**
     * Update the language the user is currently learning.
     */
    public function update(SelectedLanguageUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'selected_language_id' => $request->validated('language_id'),
        ]);

        return back();
    }
}
