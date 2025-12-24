<?php

declare(strict_types=1);

namespace Esanj\AppService\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class ServiceException extends Exception
{
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 400);
    }

    public static function clientIdRequired(): self
    {
        return new self('Client ID is required.', 422);
    }

    public static function serviceNotFound(string $identifier = ''): self
    {
        return new self(
            'Service not found.',
            404,
            context: ['identifier' => $identifier]
        );
    }

    public static function serviceInactive(string $serviceName = ''): self
    {
        return new self(
            'Service is currently inactive.',
            403,
            context: ['service' => $serviceName]
        );
    }

    public static function permissionDenied(string $permission = ''): self
    {
        return new self(
            'Access denied for this service.',
            403,
            context: ['permission' => $permission]
        );
    }

    public static function clientValidationFailed(string $clientId, string $error): self
    {
        return new self(
            "Client validation failed: {$error}",
            400,
            context: ['client_id' => $clientId]
        );
    }
}