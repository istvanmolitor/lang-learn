<?php

namespace App\Http\Controllers;

use App\Http\Requests\SelectedLanguageUpdateRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\RedirectResponse;

class SelectedLanguageController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Update the language the user is currently learning.
     */
    public function update(SelectedLanguageUpdateRequest $request): RedirectResponse
    {
        $this->users->updateSelectedLanguage($request->user(), $request->validated('language_id'));

        return back();
    }
}
