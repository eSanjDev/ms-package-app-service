<?php

declare(strict_types=1);

namespace Esanj\AppService\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtException extends UnauthorizedHttpException
{
    public static function missingToken(): self
    {
        return new self('Bearer realm="Service"', 'Authorization token is missing.');
    }

    public static function invalidToken(string $reason = ''): self
    {
        $message = 'Invalid token or signature.';
        if ($reason !== '') {
            $message .= " Reason: {$reason}";
        }

        return new self('Bearer Token', $message);
    }

    public static function expiredToken(): self
    {
        return new self('Bearer Token', 'Token has expired.');
    }

    public static function invalidAudience(): self
    {
        return new self('Bearer Token', 'Token audience is invalid.');
    }
}