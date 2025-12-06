<?php

namespace Esanj\AppService\Services;

use Esanj\AppService\Model\Service;
use Esanj\AuthBridge\Services\ClientCredentialsService;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ServiceService
{
    public function __construct(
        protected ClientCredentialsService $credentialsService
    ) {}

    public function getServicesWithPaginate(Request $request): LengthAwarePaginator
    {
        $query = Service::query();

        if ($request->boolean('only_trash')) {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('client_id', 'like', "%{$searchTerm}%");
            });
        }

        return $query->paginate($request->integer('per_page', 10));
    }

    public function findById(int $id): Service
    {
        return Service::query()->findOrFail($id);
    }

    public function findByIdWithTrashed(int $id): Service
    {
        return Service::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Service
    {
        return Service::query()->create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }

    public function restore(int $id): bool
    {
        return $this->findByIdWithTrashed($id)->restore();
    }

    public function syncPermissions(Service $service, ?array $permissionIds): void
    {
        $service->permissions()->sync($permissionIds ?? []);
    }

    public function getClientDetails(string $clientId): Response
    {
        $token = $this->credentialsService->getAccessToken(
            config('auth_bridge.client_id'),
            config('auth_bridge.client_secret')
        );

        if (empty($token['access_token'])) {
            throw new RuntimeException('Access token not found.');
        }

        $url = config('auth_bridge.base_url') . "/api/application/clients/{$clientId}";

        return Http::withToken($token['access_token'])->get($url);
    }

    public function decodeJWT(string $token): object
    {
        $publicKeyPath = config('esanj.manager.public_key_path');

        if (!$publicKeyPath || !file_exists($publicKeyPath)) {
            Log::error('ServiceService: Public key file not found.', [
                'path' => $publicKeyPath,
            ]);
            throw new RuntimeException('Public key file not found.');
        }

        try {
            $publicKey = file_get_contents($publicKeyPath);
            return JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (Exception $e) {
            Log::error('JWT validation error: ' . $e->getMessage());
            throw new UnauthorizedHttpException('Bearer Token', 'Invalid token or signature.');
        }
    }
}