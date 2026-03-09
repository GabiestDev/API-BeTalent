<?php

use App\Models\Gateway;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    // ensure DB gateways exist
    Gateway::create(['name' => 'Gateway One', 'is_active' => true, 'priority' => 1]);
    Gateway::create(['name' => 'Gateway Two', 'is_active' => true, 'priority' => 2]);
    Product::create(['name' => 'Sample', 'amount' => 1000]);
});

it('charges successfully on gateway one', function () {
    Http::fake([
        '*' => Http::response(['success' => true, 'transaction_id' => (string) Str::random(10)], 200),
    ]);

    $payload = [
        'product_id' => 1,
        'quantity' => 1,
        'client_name' => 'John Doe',
        'client_email' => 'john@example.com',
        'card_number' => '4242424242424242',
        'cvv' => '123',
    ];

    $response = $this->postJson('/api/checkout', $payload);
    $response->assertStatus(201);

    $body = $response->json('transaction');
    expect($body['status'])->toBe('paid');
    expect($body['gateway_id'])->toBe(1);
});

it('falls back to gateway two when gateway one fails', function () {
    Http::fake([
        'http://localhost:3001/login' => Http::response(['token' => 'tkn'], 200),
        'http://localhost:3001/*' => Http::response(['success' => false, 'message' => 'Invalid CVV'], 422),
        'http://localhost:3002/*' => Http::response(['success' => true, 'transaction_id' => (string) Str::random(10)], 200),
    ]);

    $payload = [
        'product_id' => 1,
        'quantity' => 1,
        'client_name' => 'Jane Doe',
        'client_email' => 'jane@example.com',
        'card_number' => '4000000000000002',
        'cvv' => '000',
    ];

    $response = $this->postJson('/api/checkout', $payload);
    $response->assertStatus(201);

    $body = $response->json('transaction');
    expect($body['status'])->toBe('paid');
    expect($body['gateway_id'])->toBe(2);
});
