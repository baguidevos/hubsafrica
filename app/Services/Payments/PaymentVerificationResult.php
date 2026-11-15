<?php

namespace App\Services\Payments;

final readonly class PaymentVerificationResult
{
    public function __construct(
        public bool $isPaid,
        public string $externalReference,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
    ) {}

    public static function paid(string $externalReference): self
    {
        return new self(
            isPaid: true,
            externalReference: $externalReference,
        );
    }

    public static function failed(string $externalReference, string $errorCode, string $errorMessage): self
    {
        return new self(
            isPaid: false,
            externalReference: $externalReference,
            errorCode: $errorCode,
            errorMessage: $errorMessage,
        );
    }
}
