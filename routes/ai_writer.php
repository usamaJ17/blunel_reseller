<?php

use App\Http\Controllers\Admin\Addons\AIWriterController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;


Route::get('ai-writer', [AIWriterController::class,'getAIContent'])->name('ai-writer.get-content');


Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group(
        [
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath','isInstalled']
        ], function () {

        Route::middleware(['adminCheck','loginCheck'])->prefix('admin')->group(function () {
            Route::get('ai-writer-setting', [AIWriterController::class,'config'])->name('ai-writer.config');
        });
        Route::middleware('loginCheck')->group(function () {
            Route::get('admin/config-user-review', [AIWriterController::class, 'configReviewOption'])->name('admin.config.review.option');
            Route::put('seller/config-user-review', [AIWriterController::class, 'configReviewOption'])->name('config.review.option');
            Route::post('admin/get/ai-content', [AIWriterController::class, 'aiContent'])->name('ai.content');

        });
    });
});
