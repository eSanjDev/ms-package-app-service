<?php

namespace Esanj\AppService\Http\Middleware;

use Closure;
use Esanj\AppService\Exceptions\JwtException;
use Esanj\AppService\Services\ServiceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateJwtMiddleware
{
    public function __construct(
        protected ServiceService $serviceService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw JwtException::missingToken();
        }

        try {
            $decoded = $this->serviceService->decodeJWT($token);

            $request->attributes->set('jwt_client_id', $decoded->aud ?? null);
            $request->attributes->set('jwt_payload', $decoded);

        } catch (Exception $e) {
            Log::error('ValidateJwtMiddleware: JWT validation error', [
                'message' => $e->getMessage(),
            ]);
            throw JwtException::invalidToken();
        }

        return $next($request);
    }
}