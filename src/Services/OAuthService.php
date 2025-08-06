<?php

namespace Esanj\AppService\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OAuthService
{
    public function getAccessToken(): ?array
    {
        $cacheKey = md5("app_service_client_credentials_token");

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Request a new token from the Accounting microservice
        $response = Http::asForm()->post(config('app_service.accounting_base_url') . '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => config('app_service.client_id'),
            'client_secret' => config('app_service.secret_client'),
            'scope' => '*',
        ]);

        if ($response->failed()) {
            Log::channel('emergency')->alert('fail oauth', [
                'service' => 'ClientCredentialsService',
                'json' => $response->json(),
                'status' => $response->getStatusCode(),
            ]);
            return null;
        }

        $tokenData = $response->json();
        $expiresIn = $tokenData['expires_in'] ?? 3600;

        Cache::put($cacheKey, $tokenData, $expiresIn - 60);

        return $tokenData;
    }
}
