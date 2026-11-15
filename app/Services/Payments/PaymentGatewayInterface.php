<?php

namespace App\Services\Payments;

use App\Models\AccompanimentTool;
use App\Models\AccompanimentToolPayment;
use App\Models\User;

interface PaymentGatewayInterface
{
    public function providerName(): string;

    public function initiateCheckout(
        AccompanimentToolPayment $payment,
        AccompanimentTool $tool,
        User $buyer,
        string $callbackUrl,
    ): PaymentCheckoutSession;

    /**
     * @param  array<string, mixed>  $callbackPayload
     */
    public function verifyCallback(
        AccompanimentToolPayment $payment,
        array $callbackPayload,
    ): PaymentVerificationResult;
}
