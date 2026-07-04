<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\HealthController;
use App\Http\Controllers\API\RegisterController;

Route::get('/health', HealthController::class);

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [RegisterController::class, 'login']);
