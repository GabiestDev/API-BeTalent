<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return response()->json(Client::all());
    }

    public function show($id)
    {
        $client = Client::with('transactions.product', 'transactions.gateway')->findOrFail($id);

        return response()->json($client);
    }
}
