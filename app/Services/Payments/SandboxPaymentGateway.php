<?php

namespace App\Services\Payments;

use App\Models\AccompanimentTool;
use App\Models\AccompanimentToolPayment;
use App\Models\User;
use Illuminate\Support\Str;

class SandboxPaymentGateway implements PaymentGatewayInterface
{
    public function providerName(): string
    {
        return 'sandbox';
    }

    public function initiateCheckout(
        AccompanimentToolPayment $payment,
        AccompanimentTool $tool,
        User $buyer,
        string $callbackUrl,
    ): PaymentCheckoutSession {
        $externalReference = 'sbx_'.Str::lower(Str::random(16));

        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $checkoutUrl = $callbackUrl.$separator.http_build_query([
            'status' => 'approved',
            'transaction_id' => $externalReference,
        ]);

        return new PaymentCheckoutSession(
            provider: $this->providerName(),
            externalReference: $externalReference,
            checkoutUrl: $checkoutUrl,
            isInstantSandbox: true,
        );
    }

    public function verifyCallback(
        AccompanimentToolPayment $payment,
        array $callbackPayload,
    ): PaymentVerificationResult {
        $status = (string) ($callbackPayload['status'] ?? '');
        $externalReference = (string) ($callbackPayload['transaction_id'] ?? $payment->external_reference ?? $payment->reference);

        if ($status === 'approved') {
            return PaymentVerificationResult::paid($externalReference);
        }

        return PaymentVerificationResult::failed(
            externalReference: $externalReference,
            errorCode: 'PAYMENT_DECLINED',
            errorMessage: 'Le paiement sandbox n\'a pas été confirmé.',
        );
    }
}
