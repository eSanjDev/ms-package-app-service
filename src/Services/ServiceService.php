<?php

namespace Esanj\AppService\Services;

use Illuminate\Support\Facades\Http;
use Esanj\AppService\Services\OAuthService;

class ServiceService
{
    public function __construct(protected OAuthService $oauthService)
    {
    }

    public function getClientDeteils(string $client_id)
    {
        $token = $this->oauthService->getAccessToken();

        if (empty($token['access_token'])) {
            throw new RuntimeException('Access token not found');
        }

        $url = config('app_service.accounting_base_url') . "/api/application/clients/{$client_id}";

        $response = Http::withToken($token['access_token'])->get($url);

        if ($response->failed()) {
            return response()->json($response->json(), $response->status());
        }


        return $response->json();
    }
}
