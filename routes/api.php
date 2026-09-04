<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('imports', ImportController::class)->only(['show', 'store']);

Route::get('properties', [PropertyController::class, 'index'])->name('properties.index');

Route::post('offers/{offer}/reservations', [ReservationController::class, 'store'])
    ->name('offers.reservations.store');
