<?php

namespace App\Traits\Vrm\Model;


trait ApiResponseTrait
{
    /**
     * Return a success JSON response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success($data = null, string $message = '', int $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if (!is_null($data)) {
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

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  array  $errors
     * @param  mixed  $data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = '', int $statusCode = 400, array $errors = [], $data = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        if (config('app.debug')) {
            $response['debug'] = [
                'execution_time' => microtime(true) - LARAVEL_START,
                'memory_usage' => memory_get_usage(),
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a not found JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFound(string $message = 'Resource not found')
    {
        return $this->error($message, 404);
    }

    /**
     * Return a validation error JSON response.
     *
     * @param  array  $errors
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationError(array $errors, string $message = 'Validation failed')
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return an unauthorized JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorized(string $message = 'Unauthorized')
    {
        return $this->error($message, 401);
    }

    /**
     * Return a forbidden JSON response.
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbidden(string $message = 'Forbidden')
    {
        return $this->error($message, 403);
    }
}
