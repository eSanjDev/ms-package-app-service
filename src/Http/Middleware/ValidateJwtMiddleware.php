<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Middleware;

use Closure;
use Esanj\AppService\Contracts\ServiceServiceInterface;
use Esanj\AppService\Exceptions\JwtException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ValidateJwtMiddleware
{
    private const ATTRIBUTE_CLIENT_ID = 'jwt_client_id';
    private const ATTRIBUTE_PAYLOAD = 'jwt_payload';

    public function __construct(
        protected ServiceServiceInterface $serviceService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            throw JwtException::missingToken();
        }

        try {
            $decoded = $this->serviceService->decodeJWT($token);

            $request->attributes->set(self::ATTRIBUTE_CLIENT_ID, $decoded->aud ?? null);
            $request->attributes->set(self::ATTRIBUTE_PAYLOAD, $decoded);
        } catch (HttpExceptionInterface $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('ValidateJwtMiddleware: Unexpected error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw JwtException::invalidToken();
        }

        return $next($request);
    }
}