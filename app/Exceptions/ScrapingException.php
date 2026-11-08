<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class ScrapingException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode,
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function invalidUrl(string $url): self
    {
        return new self(
            message: "L'adresse URL fournie est invalide : {$url}",
            errorCode: 'INVALID_URL',
        );
    }

    public static function privateHostForbidden(string $host): self
    {
        return new self(
            message: "L'accès aux adresses internes ou locales est interdit : {$host}",
            errorCode: 'PRIVATE_HOST_FORBIDDEN',
        );
    }

    public static function unreachableHost(string $url, string $reason): self
    {
        return new self(
            message: "Impossible d'accéder à la page ({$url}) : {$reason}",
            errorCode: 'UNREACHABLE_HOST',
        );
    }

    public static function invalidImagePayload(string $imageUrl, string $reason): self
    {
        return new self(
            message: "Le fichier image distant n'a pas pu être validé ({$imageUrl}) : {$reason}",
            errorCode: 'INVALID_IMAGE_PAYLOAD',
        );
    }
}
