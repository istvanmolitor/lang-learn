<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the dashboard.
     */
    public function index(): Response
    {
        $user = request()->user();

        return Inertia::render('Dashboard', [
            'learnableLanguages' => $user->languages()
                ->when($user->language_id, fn ($query, $languageId) => $query->where('languages.id', '!=', $languageId))
                ->orderBy('languages.name')
                ->get(['languages.id', 'languages.code', 'languages.name', 'languages.native_name']),
            'selectedLanguageId' => $user->selected_language_id,
        ]);
    }
}
