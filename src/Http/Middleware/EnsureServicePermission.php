<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Middleware;

use Closure;
use Esanj\AppService\Contracts\ServiceServiceInterface;
use Esanj\AppService\Exceptions\JwtException;
use Esanj\AppService\Exceptions\ServiceException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class EnsureServicePermission
{
    private const ATTRIBUTE_SERVICE = 'service';

    public function __construct(
        protected ServiceServiceInterface $serviceService
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            throw JwtException::missingToken();
        }

        try {
            $decoded = $this->serviceService->decodeJWT($token);
            $clientId = $decoded->aud ?? null;

            if (empty($clientId)) {
                throw JwtException::invalidAudience();
            }

            $service = $this->serviceService->findByClientId($clientId);

            if ($service === null) {
                Log::warning('EnsureServicePermission: Unknown service client_id', [
                    'client_id' => $clientId,
                ]);

                return $this->denyAccess(__('Service cannot be identified.'));
            }

            if (! $service->is_active) {
                Log::info('EnsureServicePermission: Service is inactive', [
                    'service' => $service->name,
                ]);

                return $this->denyAccess(__('Service is currently inactive.'));
            }

            if (! $service->hasPermission($permission)) {
                Log::info('EnsureServicePermission: Permission denied', [
                    'service' => $service->name,
                    'permission' => $permission,
                ]);

                return $this->denyAccess(__('Access denied for this service.'));
            }

            $request->attributes->set(self::ATTRIBUTE_SERVICE, $service);

            return $next($request);
        } catch (UnauthorizedHttpException $e) {
            throw $e;
        } catch (ServiceException $e) {
            return $this->denyAccess($e->getMessage());
        } catch (Throwable $e) {
            Log::error('EnsureServicePermission: Unexpected error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return $this->denyAccess(__('An internal authorization error occurred.'));
        }
    }

    protected function denyAccess(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}