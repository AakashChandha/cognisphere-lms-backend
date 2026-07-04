<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (API-only backend — no UI)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'name' => 'Cognisphere LMS API',
        'status' => 'ok',
        'docs' => url('/api/health'),
    ]);
});
