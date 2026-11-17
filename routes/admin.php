<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\EnquiryController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GhlController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RecaptchaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TourTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('enquiries/export', [EnquiryController::class, 'export'])->name('enquiries.export');
        Route::post('enquiries/{enquiry}/ghl-retry', [EnquiryController::class, 'retryGhl'])->name('enquiries.ghl-retry');
        Route::resource('enquiries', EnquiryController::class)->only(['index', 'show', 'update', 'destroy']);

        Route::post('packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::post('packages/{package}/toggle/{field}', [PackageController::class, 'toggle'])->name('packages.toggle');
        Route::post('packages/reorder', [PackageController::class, 'reorder'])->name('packages.reorder');
        Route::delete('packages/{package}/images/{image}', [PackageController::class, 'destroyImage'])->name('packages.images.destroy');
        Route::resource('packages', PackageController::class)->except('show');

        Route::resource('tour-types', TourTypeController::class)->except('show')->parameters(['tour-types' => 'tourType']);
        Route::resource('destinations', DestinationController::class)->except('show');
        Route::resource('experiences', ExperienceController::class)->except('show');

        Route::get('quiz', [QuizController::class, 'index'])->name('quiz.index');
        Route::post('quiz/steps', [QuizController::class, 'storeStep'])->name('quiz.steps.store');
        Route::put('quiz/steps/{step}', [QuizController::class, 'updateStep'])->name('quiz.steps.update');
        Route::delete('quiz/steps/{step}', [QuizController::class, 'destroyStep'])->name('quiz.steps.destroy');
        Route::post('quiz/steps/{step}/options', [QuizController::class, 'storeOption'])->name('quiz.options.store');
        Route::put('quiz/options/{option}', [QuizController::class, 'updateOption'])->name('quiz.options.update');
        Route::delete('quiz/options/{option}', [QuizController::class, 'destroyOption'])->name('quiz.options.destroy');

        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');

        Route::resource('testimonials', TestimonialController::class)->except('show');
        Route::resource('faqs', FaqController::class)->except('show');
        Route::resource('posts', PostController::class)->except('show');
        Route::resource('team', TeamMemberController::class)->except('show')->parameters(['team' => 'team']);

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('ghl', [GhlController::class, 'edit'])->name('ghl.edit');
        Route::put('ghl', [GhlController::class, 'update'])->name('ghl.update');
        Route::post('ghl/test', [GhlController::class, 'test'])->name('ghl.test');
        Route::post('ghl/setup', [GhlController::class, 'setup'])->name('ghl.setup');
        Route::get('recaptcha', [RecaptchaController::class, 'edit'])->name('recaptcha.edit');
        Route::put('recaptcha', [RecaptchaController::class, 'update'])->name('recaptcha.update');
        Route::post('recaptcha/test', [RecaptchaController::class, 'test'])->name('recaptcha.test');

        Route::resource('users', AdminUserController::class)->except('show')->parameters(['users' => 'user']);
    });
});
