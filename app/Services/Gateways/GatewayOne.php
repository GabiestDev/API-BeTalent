<?php

namespace App\Services\Gateways;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class GatewayOne implements PaymentGatewayInterface
{
    protected string $base;

    protected ?string $token = null;

    public function __construct()
    {
        $this->base = env('GATEWAY_ONE_URL', 'http://localhost:3001');
    }

    protected function authenticate(): void
    {
        if ($this->token) {
            return;
        }

        $resp = Http::post($this->base.'/login', [
            'email' => env('GATEWAY_ONE_LOGIN', 'dev@betalent.tech'),
            'token' => env('GATEWAY_ONE_TOKEN', 'FEC9BB078BF338F464F96B48089EB498'),
        ]);

        $this->token = Arr::get($resp->json(), 'token') ?? Arr::get($resp->json(), 'access_token') ?? null;
    }

    public function charge(array $data): array
    {
        $this->authenticate();

        $payload = [
            'amount' => $data['amount'],
            'name' => $data['client']['name'] ?? null,
            'email' => $data['client']['email'] ?? null,
            'cardNumber' => $data['card_number'] ?? null,
            'cvv' => $data['cvv'] ?? null,
        ];

        $resp = Http::withToken($this->token)->post($this->base.'/transactions', $payload);

        return $resp->json();
    }

    public function refund(string $transactionId): array
    {
        $this->authenticate();

        $resp = Http::withToken($this->token)->post($this->base."/transactions/{$transactionId}/charge_back");

        return $resp->json();
    }
}
