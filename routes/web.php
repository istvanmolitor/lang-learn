<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\CardGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelectedLanguageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return redirect()->route($request->user() ? 'dashboard' : 'login');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::put('selected-language', [SelectedLanguageController::class, 'update'])->name('selected-language.update');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('cards', CardController::class);
    Route::resource('card-groups', CardGroupController::class);
    Route::get('card-groups-ai/create', [CardGroupController::class, 'createAi'])->name('card-groups.create-ai');
    Route::post('card-groups-ai', [CardGroupController::class, 'storeAi'])->name('card-groups.store-ai');
    Route::post('card-groups/{cardGroup}/generate-cards', [CardGroupController::class, 'generateCards'])->name('card-groups.generate-cards');
    Route::get('card-groups/{cardGroup}/export', [CardGroupController::class, 'export'])->name('card-groups.export');
    Route::post('card-groups/{cardGroup}/import', [CardGroupController::class, 'import'])->name('card-groups.import');

    Route::get('card-groups/{cardGroup}/learn', [LearnController::class, 'show'])->name('card-groups.learn');
    Route::post('card-groups/{cardGroup}/learn', [LearnController::class, 'store'])->name('card-groups.learn.answer');

    Route::resource('articles', ArticleController::class)->only(['index', 'show']);
});

require __DIR__.'/auth.php';
