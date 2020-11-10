<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Http\Middleware\RefreshToken;

class RefreshJWTToken extends RefreshToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->checkForToken($request);

        try {
            // Try to refresh the token normally
            $token = $this->auth->parseToken()->refresh();
        } catch (TokenExpiredException $e) {
            // If the token was expired, refresh it automatically
            $token = $this->auth->refresh($this->auth->getToken());
            $this->auth->setToken($token)->toUser();
        } catch (JWTException $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage(), $e, $e->getCode());
        }

        $response = $next($request);

        // Send the refreshed token back to the client.
        return $this->setAuthenticationHeader($response, $token);
    }
}
