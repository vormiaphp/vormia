<?php

namespace Vormia\Vormia\Traits\Model;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    protected function success($data = null, string $message = '', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'execution_time' => microtime(true) - LARAVEL_START,
                'memory_usage' => memory_get_usage(),
            ];
        }

        return response()->json($response, $statusCode);
    }

    protected function error(string $message = '', int $statusCode = 400, array $errors = [], $data = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'execution_time' => microtime(true) - LARAVEL_START,
                'memory_usage' => memory_get_usage(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
            ];
        }

        return response()->json($response, $statusCode);
    }

    protected function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 403);
    }
}
