<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render exceptions as JSON for API clients (never expose raw SQL/errors to UI).
     */
    public function render($request, Throwable $e)
    {
        if ($this->shouldReturnJson($request)) {
            return $this->renderApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    protected function shouldReturnJson(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    protected function renderApiException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->apiError(
                'Validation failed. Please check your input.',
                $e->errors(),
                422
            );
        }

        if ($e instanceof AuthenticationException) {
            return $this->apiError(
                'Authentication required.',
                ['error' => 'Please log in to continue.'],
                401
            );
        }

        if ($e instanceof QueryException) {
            Log::error('API database error', [
                'url' => $request->fullUrl(),
                'message' => $e->getMessage(),
            ]);

            return $this->apiError(
                'We could not complete your request. Please verify your details and try again.',
                $this->debugDetails($e),
                500
            );
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->apiError(
                'The requested resource was not found.',
                ['error' => 'Endpoint not found.'],
                404
            );
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->apiError(
                'This HTTP method is not allowed for the requested endpoint.',
                ['error' => 'Method not allowed.'],
                405
            );
        }

        if ($e instanceof HttpExceptionInterface) {
            return $this->apiError(
                $this->messageForHttpStatus($e->getStatusCode()),
                ['error' => 'Request could not be processed.'],
                $e->getStatusCode()
            );
        }

        Log::error('API unhandled exception', [
            'url' => $request->fullUrl(),
            'message' => $e->getMessage(),
            'exception' => get_class($e),
        ]);

        return $this->apiError(
            'Something went wrong. Please try again later.',
            $this->debugDetails($e),
            500
        );
    }

    protected function apiError(string $message, mixed $data, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data ?: (object) [],
        ], $status);
    }

    protected function debugDetails(Throwable $e): array|object
    {
        if (! config('app.debug')) {
            return (object) [];
        }

        return [
            'error' => $e->getMessage(),
            'exception' => class_basename($e),
        ];
    }

    protected function messageForHttpStatus(int $status): string
    {
        return match ($status) {
            400 => 'Bad request. Please check the data you submitted.',
            401 => 'Authentication required.',
            403 => 'You do not have permission to perform this action.',
            404 => 'The requested resource was not found.',
            405 => 'This HTTP method is not allowed.',
            422 => 'Validation failed. Please check your input.',
            429 => 'Too many requests. Please wait and try again.',
            default => 'Request could not be processed.',
        };
    }
}
