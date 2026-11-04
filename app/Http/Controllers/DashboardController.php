<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UserRepositoryInterface;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Show the dashboard.
     */
    public function index(): Response
    {
        $user = request()->user();

        return Inertia::render('Dashboard', [
            'learnableLanguages' => $this->users->learnableLanguagesFor(
                $user,
                ['languages.id', 'languages.code', 'languages.name', 'languages.native_name']
            ),
            'selectedLanguageId' => $user->selected_language_id,
        ]);
    }
}
