<?php

namespace App\Support;

final readonly class AccompanimentToolAccessDecision
{
    public function __construct(
        public bool $canAccess,
        public string $code,
        public string $message,
    ) {}

    public static function granted(): self
    {
        return new self(
            canAccess: true,
            code: 'ACCESS_GRANTED',
            message: 'Accès autorisé à cet outil d\'accompagnement.',
        );
    }

    public static function denied(string $code, string $message): self
    {
        return new self(
            canAccess: false,
            code: $code,
            message: $message,
        );
    }
}
