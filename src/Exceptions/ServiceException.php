<?php

namespace Esanj\AppService\Exceptions;

use Exception;

class ServiceException extends Exception
{
    public static function clientIdRequired(): self
    {
        return new self('Client ID is required.', 422);
    }

    public static function serviceNotFound(): self
    {
        return new self('Service not found.', 404);
    }

    public static function serviceInactive(): self
    {
        return new self('Service is currently inactive.', 403);
    }

    public static function permissionDenied(): self
    {
        return new self('Access denied for this service.', 403);
    }
}