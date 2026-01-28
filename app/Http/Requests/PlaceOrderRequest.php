<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:card,cod,paypal'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Please enter your full name.'),
            'email.required' => __('Please enter your email address.'),
            'email.email' => __('Please enter a valid email address.'),
            'address.required' => __('Please enter your delivery address.'),
            'payment_method.required' => __('Please select a payment method.'),
            'payment_method.in' => __('Please select a valid payment method.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('full name'),
            'email' => __('email address'),
            'address' => __('delivery address'),
            'postal_code' => __('postal code'),
            'payment_method' => __('payment method'),
        ];
    }
}
