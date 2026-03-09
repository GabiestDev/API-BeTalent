<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $transactions = Transaction::with(['client', 'product', 'gateway'])->paginate($perPage);

        return response()->json($transactions);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['client', 'product', 'gateway'])->findOrFail($id);

        return response()->json($transaction);
    }

    public function refund($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'paid') {
            return response()->json(['message' => 'Only paid transactions can be refunded.'], 422);
        }

        $gateway = Gateway::find($transaction->gateway_id);
        if (! $gateway) {
            return response()->json(['message' => 'Gateway not found for transaction.'], 404);
        }

        // resolve adapter
        $adapter = null;
        $map = [
            'Gateway One' => \App\Services\Gateways\GatewayOne::class,
            'Gateway Two' => \App\Services\Gateways\GatewayTwo::class,
        ];

        if (isset($map[$gateway->name])) {
            $adapter = app()->make($map[$gateway->name]);
        }

        if (! $adapter) {
            return response()->json(['message' => 'No adapter for gateway.'], 500);
        }

        try {
            $result = $adapter->refund($transaction->external_id);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Refund failed: '.$e->getMessage()], 500);
        }

        if (is_array($result) && ($result['success'] ?? false) === true) {
            $transaction->status = 'refunded';
            $transaction->save();

            return response()->json(['message' => 'Refunded successfully']);
        }

        return response()->json(['message' => $result['message'] ?? 'Refund failed'], 422);
    }
}
