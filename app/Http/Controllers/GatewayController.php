<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function index()
    {
        return response()->json(Gateway::all());
    }

    public function update(Request $request, $id)
    {
        $gateway = Gateway::findOrFail($id);
        $data = $request->validate([
            'is_active' => ['boolean'],
            'priority' => ['integer'],
            'name' => ['string'],
        ]);

        $gateway->fill($data);
        $gateway->save();

        return response()->json($gateway);
    }
}
