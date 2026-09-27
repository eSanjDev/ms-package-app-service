<?php

declare(strict_types=1);

namespace Esanj\AppService\Http\Traits;

use Esanj\AppService\Exceptions\ServiceException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ValidatesClients
{
    public function validateClient(Request $request): JsonResponse
    {
        $this->authorizeClientLookup($request);

        $clientId = $request->input('client_id');

        if (!is_string($clientId) || trim($clientId) === '') {
            throw ServiceException::clientIdRequired();
        }

        try {
            $response = $this->serviceService->getClientDetails($clientId);
        } catch (ConnectionException) {
            throw ServiceException::clientValidationFailed($clientId, 'the account service could not be reached.');
        }

        if ($response->failed()) {
            $message = $response->json('message');

            throw ServiceException::clientValidationFailed(
                $clientId,
                is_string($message) && $message !== '' ? $message : 'the account service answered ' . $response->status() . '.'
            );
        }

        return response()->json(['data' => ['name' => $response->json('data.name')]]);
    }

    private function authorizeClientLookup(Request $request): void
    {
        $manager = $request->user('manager');
        $access = config('esanj.app_service.access_provider');

        if ($manager === null
            || (!$manager->hasPermission($access['store']) && !$manager->hasPermission($access['update']))) {
            throw ServiceException::clientLookupDenied($access['store'], $access['update']);
        }
    }
}
