<?php

namespace Esanj\AppService\Services;

use Esanj\AuthBridge\Services\ClientCredentialsService;
use Illuminate\Support\Facades\Http;

class ServiceService
{
    public function __construct(protected ClientCredentialsService $credentialsService)
    {
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
}
