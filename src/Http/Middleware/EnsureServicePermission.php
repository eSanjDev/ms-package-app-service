<?php

namespace Esanj\AppService\Http\Middleware;

use Closure;
use Esanj\AppService\Model\Service;
use Esanj\AppService\Services\ServiceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EnsureServicePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new UnauthorizedHttpException(
                'Bearer realm="Service"',
                __('Authorization token is missing.')
            );
        }

        try {
            $decoded = app(ServiceService::class)->decodeJWT($token);

            /** @var Service|null $service */
            $service = Service::query()->where('client_id', $decoded->aud ?? null)->first();

            if (!$service) {
                Log::warning('EnsureServicePermission: Unknown service client_id', [
                    'client_id' => $decoded->aud ?? null,
                ]);
                return $this->deny(__('Service cannot be identified.'));
            }

            if (!$service->is_active) {
                Log::info('EnsureServicePermission: Service is inactive', [
                    'service' => $service->name,
                ]);
                return $this->deny(__('Service is currently inactive.'));
            }

            if (!$service->hasPermission($permission)) {
                Log::info('EnsureServicePermission: Permission denied', [
                    'service' => $service->name,
                    'permission' => $permission,
                ]);
                return $this->deny(__('Access denied for this service.'));
            }

            return $next($request);

        } catch (UnauthorizedHttpException $e) {
            throw $e;

        } catch (Exception $e) {
            Log::error('EnsureServicePermission Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->deny(__('An internal authorization error occurred.'));
        }
    }

    /**
     * Return standardized access denied response.
     */
    protected function deny(string $message): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}
