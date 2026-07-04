<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (API-only backend — no UI)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'success' => true,
        'message' => 'Cognisphere LMS API is running',
        'data' => [
            'name' => 'Cognisphere LMS API',
            'status' => 'ok',
            'docs' => url('/api/documentation'),
            'health' => url('/api/health'),
        ],
    ]);
});
