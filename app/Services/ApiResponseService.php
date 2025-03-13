<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Exception;

class ApiResponseService
{


    /**
     * Generate a success response.
     */
    public static function successResponse($data, $message, $statusCode = 200, $meta = null)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        if ($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }


    /**
     * Handle validation errors.
     */
    public static function handleValidationError(ValidationException $exception)
    {
        $errors = $exception->errors();
        $firstErrorMessage = collect($errors)->first()[0];

        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => $firstErrorMessage,
            'errors' => $errors
        ], 422);
    }

    /**
     * Handle unexpected errors.
     */
    public static function handleUnexpectedError(Exception $exception)
    {
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => 'Something went wrong',
            'errors' => $exception->getMessage()
        ], 500);
    }

    /**
     * Generate general error responses.
     */
    public static function errorResponse($message, $statusCode)
    {
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }
}
