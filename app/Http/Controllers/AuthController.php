<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Services\ApiResponseService;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        try {
            $user = $this->authService->registerUser($request);

            return ApiResponseService::successResponse(
                ['user' => $user],
                'User registered successfully',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    /**
     * Login a user.
     */
    public function login(Request $request)
    {
        try {
            $authData = $this->authService->loginUser($request);

            if (!$authData) {
                return ApiResponseService::errorResponse(
                    'Invalid email or password',
                    401
                );
            }

            return ApiResponseService::successResponse(
                $authData,
                'Login successful'
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }


    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        return ApiResponseService::successResponse(
            ['user' => $request->user()],
            'User data retrieved'
        );
    }
}
