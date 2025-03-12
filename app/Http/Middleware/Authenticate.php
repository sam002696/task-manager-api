<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Handling unauthenticated requests.
     */
    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Authentication required. Please provide a valid token.'
        );
    }

    /**
     * Preventing Laravel from redirecting unauthenticated API requests.
     */
    protected function redirectTo($request): ?string
    {
        return $request->expectsJson() ? null : route('login'); //Preventing redirection to /api/login
    }
}
