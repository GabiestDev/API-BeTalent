<?php

namespace App\Services\Gateways;

interface PaymentGatewayInterface
{
    /**
     * Charge with gateway-specific payload.
     * Return array with at least ['success' => bool, ...]
     */
    public function charge(array $data): array;

    /**
     * Refund a transaction by external id.
     */
    public function refund(string $transactionId): array;
}
