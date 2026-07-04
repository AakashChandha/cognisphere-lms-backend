<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class DocumentationController extends Controller
{
    public function __invoke(): Response
    {
        if (! filter_var(env('API_DOCS_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }

        return response()->view('api.documentation');
    }
}
