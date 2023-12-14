<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;

class HorizonBasicAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws BindingResolutionException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $authenticationHasPassed =
            ($request->header('PHP_AUTH_USER') && $request->header('PHP_AUTH_PW'))
            && $request->header('PHP_AUTH_USER') === config('horizon.basic_auth.username')
            && $request->header('PHP_AUTH_PW') === config('horizon.basic_auth.password');

        if ($authenticationHasPassed === false) {
            return response()->make('Invalid credentials.', 401, ['WWW-Authenticate' => 'Basic']);
        }

        return $next($request);
    }
}
