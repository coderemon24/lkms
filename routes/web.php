<?php

use Illuminate\Support\Facades\Route;
use Lkms\Client\Http\Controllers\ActivationController;

Route::group([
    'prefix' => config('lkms.routes.prefix', 'license'),
    'middleware' => config('lkms.routes.middleware', ['web']),
], function () {
    Route::get('/activate', [ActivationController::class, 'showActivate'])->name('lkms.activate');
    Route::post('/activate', [ActivationController::class, 'submitActivate'])->name('lkms.activate.submit');
    Route::get('/locked', [ActivationController::class, 'showLocked'])->name('lkms.locked');
});
