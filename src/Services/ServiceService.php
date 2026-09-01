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
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceService implements ServiceServiceInterface
{
    private const SEARCH_COLUMNS = ['name', 'client_id'];

    private ?string $publicKey = null;

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

        $perPage = min(max($request->integer('per_page', 10), 1), 100);

        return $query->paginate($perPage);
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
        $url = "{$baseUrl}/api/application/clients/" . rawurlencode($clientId);

        $response = $this->requestWithClientToken($url);

        if ($response->status() === 401) {
            $this->credentialsService->invalidateToken(
                config('esanj.auth_bridge.client_id'),
                config('esanj.auth_bridge.client_secret'),
            );

            $response = $this->requestWithClientToken($url);
        }

        return $response;
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
        $publicKey = $this->getPublicKey();

        try {
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

    private function getPublicKey(): string
    {
        if ($this->publicKey !== null) {
            return $this->publicKey;
        }

        $inline = (string) (config('esanj.auth_bridge.public_key') ?? '');

        if (trim($inline) !== '') {
            if (! str_contains($inline, 'BEGIN PUBLIC KEY')) {
                Log::error('ServiceService: Configured public_key is not a PEM block.');
                throw new HttpException(500, 'Service authentication is not configured correctly.');
            }

            return $this->publicKey = $inline;
        }

        $path = (string) config('esanj.auth_bridge.public_key_path', storage_path('oauth-public.key'));

        if ($path === '' || ! is_readable($path)) {
            Log::error('ServiceService: Public key file not found.', ['path' => $path]);
            throw new HttpException(500, 'Service authentication is not configured correctly.');
        }

        $contents = file_get_contents($path);

        if ($contents === false || trim($contents) === '') {
            Log::error('ServiceService: Public key file is empty.', ['path' => $path]);
            throw new HttpException(500, 'Service authentication is not configured correctly.');
        }

        return $this->publicKey = $contents;
    }
}
