<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'client_name' => ['required', 'string', 'max:255'],
            'client_email' => ['required', 'email', 'max:255'],
            'card_number' => ['required', 'string', 'regex:/^[0-9]{13,19}$/'],
            'cvv' => ['required', 'string', 'regex:/^[0-9]{3,4}$/'],
        ];
    }

    public function messages()
    {
        return [
            'card_number.regex' => 'Card number must contain 13 to 19 digits.',
            'cvv.regex' => 'CVV must contain 3 or 4 digits.',
        ];
    }
}
