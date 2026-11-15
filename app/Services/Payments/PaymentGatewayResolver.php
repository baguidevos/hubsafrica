<?php

namespace App\Services\Payments;

final class PaymentGatewayResolver
{
    public static function resolve(): PaymentGatewayInterface
    {
        $driver = (string) config('services.fedapay.driver', 'sandbox');
        $secretKey = (string) config('services.fedapay.secret_key', '');

        if ($driver === 'fedapay' && $secretKey !== '') {
            return new FedaPayPaymentGateway;
        }

        return new SandboxPaymentGateway;
    }
}
