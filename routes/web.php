<?php

use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TourTypeController;
use Illuminate\Support\Facades\Route;

/*
| Public site. Every page is server-rendered Blade.
| Admin routes live in routes/admin.php (loaded below).
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{slug}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/tours', [TourTypeController::class, 'index'])->name('tours.index');
Route::get('/tours/{slug}', [TourTypeController::class, 'show'])->name('tours.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/thank-you', [PageController::class, 'thankYou'])->name('thank-you');

Route::post('/enquiry', [EnquiryController::class, 'store'])->middleware('throttle:enquiry')->name('enquiry.store');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

require __DIR__.'/admin.php';
