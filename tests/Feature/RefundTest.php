<?php

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Gateway::create(['name' => 'Gateway One', 'is_active' => true, 'priority' => 1]);
    Gateway::create(['name' => 'Gateway Two', 'is_active' => true, 'priority' => 2]);
    Product::create(['name' => 'Sample', 'amount' => 1000]);
});

it('refunds a transaction using gateway one', function () {
    $client = Client::create(['name' => 'Refund Guy', 'email' => 'refund1@example.com']);
    $transaction = Transaction::create([
        'client_id' => $client->id,
        'product_id' => 1,
        'quantity' => 1,
        'amount' => 1000,
        'status' => 'paid',
        'gateway_id' => 1,
        'external_id' => 'tx-123',
        'card_last_numbers' => '0001',
    ]);

    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $user = \App\Models\User::factory()->create();
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/transactions/'.$transaction->id.'/refund');
    $response->assertStatus(200);
    expect(Transaction::find($transaction->id)->status)->toBe('refunded');
});

it('refunds a transaction using gateway two', function () {
    $client = Client::create(['name' => 'Refund Guy 2', 'email' => 'refund2@example.com']);
    $transaction = Transaction::create([
        'client_id' => $client->id,
        'product_id' => 1,
        'quantity' => 1,
        'amount' => 1000,
        'status' => 'paid',
        'gateway_id' => 2,
        'external_id' => 'tx-abc',
        'card_last_numbers' => '0002',
    ]);

    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $user = \App\Models\User::factory()->create();
    $response = $this->actingAs($user, 'sanctum')->postJson('/api/transactions/'.$transaction->id.'/refund');
    $response->assertStatus(200);
    expect(Transaction::find($transaction->id)->status)->toBe('refunded');
});
