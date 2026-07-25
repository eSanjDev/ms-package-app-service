<?php

declare(strict_types=1);

namespace Esanj\AppService\Services;

use Esanj\AppService\Contracts\ServiceServiceInterface;
use Esanj\AppService\Exceptions\JwtException;
use Esanj\AppService\Model\Service;
use Esanj\AuthBridge\Contracts\ClientCredentialsServiceInterface;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ServiceService implements ServiceServiceInterface
{
    private const SEARCH_COLUMNS = ['name', 'client_id'];

    public function __construct(
        protected ClientCredentialsServiceInterface $credentialsService
    ) {}

    public function getServicesWithPaginate(Request $request): LengthAwarePaginator
    {
        $query = Service::query();

        if ($request->boolean('only_trash')) {
            $query->onlyTrashed();
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                foreach (self::SEARCH_COLUMNS as $column) {
                    $q->orWhere($column, 'like', "%{$searchTerm}%");
                }
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

    public function findByClientId(string $clientId): ?Service
    {
        return Service::query()->byClientId($clientId)->first();
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
        $baseUrl = rtrim(config('esanj.auth_bridge.base_url'), '/');
        $url = "{$baseUrl}/api/application/clients/{$clientId}";

        return $this->requestWithClientToken($url);
    }

    private function requestWithClientToken(string $url): Response
    {
        $tokenData = $this->credentialsService->getAccessToken(
            config('esanj.auth_bridge.client_id'),
            config('esanj.auth_bridge.client_secret')
        );

        return Http::withToken($tokenData->accessToken)->get($url);
    }

    public function decodeJWT(string $token): object
    {
        $publicKeyPath = $this->getPublicKeyPath();

        $this->validatePublicKeyPath($publicKeyPath);

        try {
            $publicKey = file_get_contents($publicKeyPath);

            return JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (ExpiredException $e) {
            Log::warning('JWT token expired', ['message' => $e->getMessage()]);
            throw JwtException::expiredToken();
        } catch (Exception $e) {
            Log::error('JWT validation error', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw JwtException::invalidToken();
        }
    }

    private function getPublicKeyPath(): string
    {
        return config('esanj.auth_bridge.public_key_path', storage_path('oauth-public.key'));
    }

    private function validatePublicKeyPath(string $path): void
    {
        if (empty($path) || ! file_exists($path)) {
            Log::error('ServiceService: Public key file not found.', ['path' => $path]);
            throw JwtException::publicKeyNotFound($path);
        }
    }
}
