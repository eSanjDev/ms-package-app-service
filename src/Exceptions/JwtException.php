<?php

namespace Esanj\AppService\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtException extends UnauthorizedHttpException
{
    public static function missingToken(): self
    {
        return new self('Bearer realm="Service"', 'Authorization token is missing.');
    }

    public static function invalidToken(): self
    {
        return new self('Bearer Token', 'Invalid token or signature.');
    }

    public static function publicKeyNotFound(): self
    {
        return new self('Bearer Token', 'Public key file not found.');
    }
}