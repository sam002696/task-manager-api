<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Exception;

class ApiResponseService
{


    /**
     * Generating a success response.
     */
    public static function successResponse($data, $message, $statusCode = 200, $meta = null)
    {
        // Returning a structured success response
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        // Adding meta if provided
        if ($meta) {
            $response['meta'] = $meta;
        }

        // Returning the response with the status code
        return response()->json($response, $statusCode);
    }


    /**
     * Handle validation errors.
     */
    public static function handleValidationError(ValidationException $exception)
    {
        // Extracting the first error message
        $errors = $exception->errors();
        $firstErrorMessage = collect($errors)->first()[0];

        // Returning a structured validation error response
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => $firstErrorMessage,
            'errors' => $errors
        ], 422);
    }

    /**
     * Handling unexpected errors.
     */
    public static function handleUnexpectedError(Exception $exception)
    {
        // Returning a structured error response
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => 'Something went wrong',
            'errors' => $exception->getMessage()
        ], 500);
    }

    /**
     * Generating general error responses.
     */
    public static function errorResponse($message, $statusCode)
    {
        // Returning a structured error response
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => $message
        ], $statusCode);
    }
}
