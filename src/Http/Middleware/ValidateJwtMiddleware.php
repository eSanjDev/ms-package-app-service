<?php

namespace Esanj\AppService\Http\Middleware;

use Closure;
use Esanj\AppService\Services\ServiceService;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Middleware for validating JWT token.
 *
 * This middleware validates the JWT token in the request's Authorization header
 * against the public key provided by the Accounting microservice.
 */
class ValidateJwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new UnauthorizedHttpException('Bearer Token', 'Bearer token missing.');
        }

        try {
            $decoded = app(ServiceService::class)->decodeJWT($token);

            $request->attributes->set('jwt_client_id', $decoded->aud);

        } catch (Exception $e) {
            Log::error('JWT validation error: ' . $e->getMessage());
            throw new UnauthorizedHttpException('Bearer Token', 'Invalid token or signature.');
        }

        return $next($request);
    }
}
