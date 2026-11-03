<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->middleware('auth')->name('home');

require __DIR__.'/auth.php';
