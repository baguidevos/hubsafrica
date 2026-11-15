<?php

namespace App\Services\Payments;

use App\Models\AccompanimentTool;
use App\Models\AccompanimentToolPayment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FedaPayPaymentGateway implements PaymentGatewayInterface
{
    public function providerName(): string
    {
        return 'fedapay';
    }

    public function initiateCheckout(
        AccompanimentToolPayment $payment,
        AccompanimentTool $tool,
        User $buyer,
        string $callbackUrl,
    ): PaymentCheckoutSession {
        $secretKey = (string) config('services.fedapay.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('FEDAPAY_UNCONFIGURED: La clé secrète FedaPay est manquante.');
        }

        $baseUrl = $this->apiBaseUrl();

        $transactionResponse = Http::withToken($secretKey)
            ->acceptJson()
            ->post("{$baseUrl}/transactions", [
                'description' => "Accès outil : {$tool->title}",
                'amount' => $payment->amount,
                'currency' => ['iso' => $payment->currency],
                'callback_url' => $callbackUrl,
                'customer' => [
                    'email' => $buyer->email,
                    'firstname' => $buyer->name,
                ],
                'custom_metadata' => [
                    'payment_reference' => $payment->reference,
                    'tool_id' => $tool->id,
                    'user_id' => $buyer->id,
                ],
            ]);

        if ($transactionResponse->failed()) {
            throw new RuntimeException('FEDAPAY_TRANSACTION_FAILED: Impossible d\'initialiser la transaction FedaPay.');
        }

        $transaction = $transactionResponse->json('v1/transaction') ?? [];
        $transactionId = (string) ($transaction['id'] ?? '');

        if ($transactionId === '') {
            throw new RuntimeException('FEDAPAY_INVALID_RESPONSE: Identifiant de transaction FedaPay absent.');
        }

        $tokenResponse = Http::withToken($secretKey)
            ->acceptJson()
            ->post("{$baseUrl}/transactions/{$transactionId}/token");

        if ($tokenResponse->failed()) {
            throw new RuntimeException('FEDAPAY_TOKEN_FAILED: Impossible de générer le lien de paiement FedaPay.');
        }

        $checkoutUrl = (string) $tokenResponse->json('url');

        if ($checkoutUrl === '') {
            throw new RuntimeException('FEDAPAY_CHECKOUT_URL_MISSING: URL de paiement FedaPay absente.');
        }

        return new PaymentCheckoutSession(
            provider: $this->providerName(),
            externalReference: $transactionId,
            checkoutUrl: $checkoutUrl,
            isInstantSandbox: false,
        );
    }

    public function verifyCallback(
        AccompanimentToolPayment $payment,
        array $callbackPayload,
    ): PaymentVerificationResult {
        $secretKey = (string) config('services.fedapay.secret_key');
        $transactionId = (string) ($callbackPayload['id'] ?? $payment->external_reference ?? '');

        if ($secretKey === '' || $transactionId === '') {
            return PaymentVerificationResult::failed(
                externalReference: $transactionId,
                errorCode: 'FEDAPAY_VERIFICATION_IMPOSSIBLE',
                errorMessage: 'Impossible de vérifier la transaction FedaPay (configuration ou identifiant manquant).',
            );
        }

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->get("{$this->apiBaseUrl()}/transactions/{$transactionId}");

        if ($response->failed()) {
            return PaymentVerificationResult::failed(
                externalReference: $transactionId,
                errorCode: 'FEDAPAY_API_ERROR',
                errorMessage: 'Erreur de communication avec FedaPay lors de la vérification.',
            );
        }

        $transaction = $response->json('v1/transaction') ?? [];
        $remoteStatus = (string) ($transaction['status'] ?? '');

        if ($remoteStatus === 'approved') {
            return PaymentVerificationResult::paid($transactionId);
        }

        return PaymentVerificationResult::failed(
            externalReference: $transactionId,
            errorCode: 'PAYMENT_DECLINED',
            errorMessage: "Statut de paiement FedaPay non approuvé ({$remoteStatus}).",
        );
    }

    private function apiBaseUrl(): string
    {
        $environment = (string) config('services.fedapay.environment', 'sandbox');

        return $environment === 'live'
            ? 'https://api.fedapay.com/v1'
            : 'https://sandbox-api.fedapay.com/v1';
    }
}
