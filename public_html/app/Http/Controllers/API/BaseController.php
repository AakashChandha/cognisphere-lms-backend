<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    public function sendResponse($result, string $message, int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $result ?? (object) [],
        ], $code);
    }

    public function sendError(string $message, $errorMessages = [], int $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => ! empty($errorMessages) ? $errorMessages : (object) [],
        ], $code);
    }
}
