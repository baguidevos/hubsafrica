<?php

namespace App\Services\Payments;

final readonly class PaymentCheckoutSession
{
    public function __construct(
        public string $provider,
        public string $externalReference,
        public string $checkoutUrl,
        public bool $isInstantSandbox = false,
    ) {}
}
