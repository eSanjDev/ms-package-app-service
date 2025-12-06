<?php

namespace Esanj\AppService\Http\Middleware;

use Closure;
use Esanj\AppService\Exceptions\JwtException;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\ServiceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EnsureServicePermission
{
    public function __construct(
        protected ServiceService $serviceService
    ) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw JwtException::missingToken();
        }

        try {
            $decoded = $this->serviceService->decodeJWT($token);
            $clientId = $decoded->aud ?? null;

            $service = Service::query()->byClientId($clientId)->first();

            if (!$service) {
                Log::warning('EnsureServicePermission: Unknown service client_id', [
                    'client_id' => $clientId,
                ]);
                return $this->denyAccess(__('Service cannot be identified.'));
            }

            if (!$service->is_active) {
                Log::info('EnsureServicePermission: Service is inactive', [
                    'service' => $service->name,
                ]);
                return $this->denyAccess(__('Service is currently inactive.'));
            }

            if (!$service->hasPermission($permission)) {
                Log::info('EnsureServicePermission: Permission denied', [
                    'service' => $service->name,
                    'permission' => $permission,
                ]);
                return $this->denyAccess(__('Access denied for this service.'));
            }

            // Attach service to request for later use
            $request->attributes->set('service', $service);

            return $next($request);

        } catch (UnauthorizedHttpException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('EnsureServicePermission: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->denyAccess(__('An internal authorization error occurred.'));
        }
    }

    protected function denyAccess(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}