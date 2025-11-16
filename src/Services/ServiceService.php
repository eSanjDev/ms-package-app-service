<?php

namespace Esanj\AppService\Services;

use Esanj\AppService\Model\Service;
use Esanj\AuthBridge\Services\ClientCredentialsService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPUnit\Runner\FileDoesNotExistException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ServiceService
{
    protected Model $model;

    public function __construct(protected ClientCredentialsService $credentialsService)
    {
        $this->model = new Service();
    }

    public function getServicesWithPaginate()
    {
        $request = request();

        $query = Service::query();

        if ($request->filled('only_trash') && $request->get('only_trash') == 1) {
            $query = $query->whereNotNull('deleted_at')->withTrashed();
        }

        if ($request->filled('search')) {
            $query = $query->where(function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->get('search') . '%')
                    ->orWhere('client_id', 'like', '%' . $request->get('search') . '%');
            });
        }

        return $query->paginate($request->get('per_page', 10));
    }

    public function delete(int $id)
    {
        return $this->model->find($id)->delete();
    }

    public function restore(int $id)
    {
        return $this->model->withTrashed()->find($id)->restore();
    }

    public function getClientDetails(string $client_id)
    {
        $token = $this->credentialsService->getAccessToken(
            config('auth_bridge.client_id'),
            config('auth_bridge.client_secret')
        );

        if (empty($token['access_token'])) {
            throw new RuntimeException('Access token not found');
        }

        $url = config('auth_bridge.base_url') . "/api/application/clients/{$client_id}";

        $response = Http::withToken($token['access_token'])->get($url);

        if ($response->failed()) {
            return response()->json($response->json(), $response->status());
        }


        return $response;
    }

    public function decodeJWT(string $token)
    {
        try {
            $publicKeyPath = config('esanj.manager.public_key_path');

            if (!file_exists($publicKeyPath)) {
                Log::error('EnsureServicePermission: Public key file not found.');
                throw new FileDoesNotExistException('Public key file not found.');
            }

            $publicKey = file_get_contents($publicKeyPath);
            return JWT::decode($token, new Key($publicKey, 'RS256'));

        } catch (Exception $e) {
            Log::error('JWT validation error: ' . $e->getMessage());
            throw new UnauthorizedHttpException('Bearer Token', 'Invalid token or signature.');
        }
    }
}
