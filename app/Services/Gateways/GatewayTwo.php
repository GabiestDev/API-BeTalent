<?php

namespace App\Services\Gateways;

use Illuminate\Support\Facades\Http;

class GatewayTwo implements PaymentGatewayInterface
{
    protected string $base;

    public function __construct()
    {
        $this->base = env('GATEWAY_TWO_URL', 'http://localhost:3002');
    }

    protected function headers(): array
    {
        return [
            'Gateway-Auth-Token' => env('GATEWAY_TWO_AUTH_TOKEN', 'tk_f2198cc671b5289fa856'),
            'Gateway-Auth-Secret' => env('GATEWAY_TWO_AUTH_SECRET', '3d15e8ed6131446ea7e3456728b1211f'),
            'Accept' => 'application/json',
        ];
    }

    public function charge(array $data): array
    {
        $payload = [
            'valor' => $data['amount'],
            'nome' => $data['client']['name'] ?? null,
            'email' => $data['client']['email'] ?? null,
            'numeroCartao' => $data['card_number'] ?? null,
            'cvv' => $data['cvv'] ?? null,
        ];

        $resp = Http::withHeaders($this->headers())->post($this->base.'/transacoes', $payload);

        return $resp->json();
    }

    public function refund(string $transactionId): array
    {
        $resp = Http::withHeaders($this->headers())->post($this->base.'/transacoes/reembolso', [
            'id' => $transactionId,
        ]);

        return $resp->json();
    }
}
