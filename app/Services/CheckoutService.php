<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Arr;

class CheckoutService
{
    /**
     * Process a checkout request with automatic gateway fallback.
     */
    public function process(array $data): Transaction
    {
        $product = Product::findOrFail(Arr::get($data, 'product_id'));
        $quantity = (int) Arr::get($data, 'quantity', 1);

        $amount = $product->amount * $quantity; // amount in cents

        // Find or create client
        $client = Client::firstOrCreate(
            ['email' => Arr::get($data, 'client_email')],
            ['name' => Arr::get($data, 'client_name')]
        );

        // Get active gateways ordered by priority asc
        $gateways = Gateway::where('is_active', true)->orderBy('priority', 'asc')->get();

        $cardNumber = preg_replace('/[^0-9]/', '', Arr::get($data, 'card_number', ''));
        $cardLastNumbers = $cardNumber !== '' ? substr($cardNumber, -4) : null;

        $transactionRecord = new Transaction;
        $transactionRecord->client_id = $client->id;
        $transactionRecord->product_id = $product->id;
        $transactionRecord->quantity = $quantity;
        $transactionRecord->amount = $amount;
        $transactionRecord->card_last_numbers = $cardLastNumbers;
        $transactionRecord->status = 'failed';

        $processedGatewayId = null;
        $externalId = null;

        foreach ($gateways as $gateway) {
            // resolve adapter by gateway name
            $adapter = $this->resolveAdapter($gateway->name);
            if (! $adapter) {
                continue;
            }

            $payload = [
                'amount' => $amount,
                'currency' => 'BRL',
                'card_number' => $cardNumber,
                'cvv' => Arr::get($data, 'cvv'),
                'client' => [
                    'name' => $client->name,
                    'email' => $client->email,
                ],
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ],
            ];

            try {
                $result = $adapter->charge($payload);
            } catch (\Throwable $e) {
                $result = ['success' => false, 'message' => $e->getMessage()];
            }

            if (is_array($result) && Arr::get($result, 'success') === true) {
                $processedGatewayId = $gateway->id;
                $externalId = Arr::get($result, 'transaction_id') ?? Arr::get($result, 'id') ?? null;
                $transactionRecord->status = 'paid';
                break; // success, stop fallback
            }

            // otherwise continue to next gateway
        }

        $transactionRecord->gateway_id = $processedGatewayId;
        $transactionRecord->external_id = $externalId;
        $transactionRecord->save();

        return $transactionRecord;
    }

    /**
     * Resolve adapter instance for a given gateway name.
     */
    protected function resolveAdapter(string $gatewayName)
    {
        $map = [
            'Gateway One' => \App\Services\Gateways\GatewayOne::class,
            'Gateway Two' => \App\Services\Gateways\GatewayTwo::class,
        ];

        if (! isset($map[$gatewayName])) {
            return null;
        }

        return app()->make($map[$gatewayName]);
    }
}
