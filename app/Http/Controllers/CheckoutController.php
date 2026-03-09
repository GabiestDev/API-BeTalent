<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;

class CheckoutController extends Controller
{
    protected CheckoutService $service;

    public function __construct(CheckoutService $service)
    {
        $this->service = $service;
    }

    public function store(CheckoutRequest $request)
    {
        $transaction = $this->service->process($request->validated());

        return response()->json(['transaction' => $transaction], 201);
    }
}
