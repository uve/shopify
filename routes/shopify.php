<?php

use App\Http\Controllers\Shopify\OAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('shopify/oauth')->name('shopify.oauth.')->group(function () {
    Route::get('/install', [OAuthController::class, 'install'])->name('install');
    Route::get('/callback', [OAuthController::class, 'callback'])->name('callback');
    Route::get('/success', [OAuthController::class, 'success'])->name('success');
});
