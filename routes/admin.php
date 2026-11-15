<?php

use Illuminate\Support\Facades\Route;

/* Admin routes are added in the admin phase. */
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', fn () => 'login')->name('login');
    Route::get('/', fn () => 'dashboard')->name('dashboard');
});
