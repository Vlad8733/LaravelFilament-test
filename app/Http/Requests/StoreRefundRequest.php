<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'type' => ['required', 'string', 'in:full,partial'],
            'amount' => ['required_if:type,partial', 'nullable', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*' => ['integer', 'exists:order_items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => __('Order reference is required.'),
            'order_id.exists' => __('Invalid order reference.'),
            'type.required' => __('Please select a refund type.'),
            'type.in' => __('Please select a valid refund type.'),
            'amount.required_if' => __('Please specify the refund amount for partial refunds.'),
            'amount.min' => __('Refund amount must be at least $0.01.'),
            'reason.required' => __('Please explain the reason for your refund request.'),
            'reason.min' => __('Please provide more details (at least 10 characters).'),
            'reason.max' => __('Reason cannot exceed 2000 characters.'),
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'full' => __('Full Refund'),
            'partial' => __('Partial Refund'),
        ];
    }
}
