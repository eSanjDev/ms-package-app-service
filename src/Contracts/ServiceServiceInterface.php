<?php

declare(strict_types=1);

namespace Esanj\AppService\Contracts;

use Esanj\AppService\Model\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

interface ServiceServiceInterface
{
    /**
     * Get paginated list of services.
     */
    public function getServicesWithPaginate(Request $request): LengthAwarePaginator;

    /**
     * Find service by ID.
     */
    public function findById(int $id): Service;

    /**
     * Find service by ID including soft deleted.
     */
    public function findByIdWithTrashed(int $id): Service;

    /**
     * Find service by client ID.
     */
    public function findByClientId(string $clientId): ?Service;

    /**
     * Create a new service.
     */
    public function create(array $data): Service;

    /**
     * Update an existing service.
     */
    public function update(Service $service, array $data): Service;

    /**
     * Delete a service.
     */
    public function delete(int $id): bool;

    /**
     * Restore a soft deleted service.
     */
    public function restore(int $id): bool;

    /**
     * Sync service permissions.
     */
    public function syncPermissions(Service $service, ?array $permissionIds): void;

    /**
     * Get client details from OAuth server.
     */
    public function getClientDetails(string $clientId): Response;

    /**
     * Decode and validate JWT token.
     */
    public function decodeJWT(string $token): object;
}