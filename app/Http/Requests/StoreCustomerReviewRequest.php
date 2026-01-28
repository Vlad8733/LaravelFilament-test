<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'delivery_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'packaging_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'product_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => __('Order reference is required.'),
            'order_id.exists' => __('Invalid order reference.'),
            'product_id.required' => __('Product reference is required.'),
            'product_id.exists' => __('Invalid product reference.'),
            'delivery_rating.required' => __('Please rate the delivery.'),
            'delivery_rating.min' => __('Rating must be at least 1 star.'),
            'delivery_rating.max' => __('Rating cannot exceed 5 stars.'),
            'packaging_rating.required' => __('Please rate the packaging.'),
            'packaging_rating.min' => __('Rating must be at least 1 star.'),
            'packaging_rating.max' => __('Rating cannot exceed 5 stars.'),
            'product_rating.required' => __('Please rate the product.'),
            'product_rating.min' => __('Rating must be at least 1 star.'),
            'product_rating.max' => __('Rating cannot exceed 5 stars.'),
            'comment.max' => __('Comment cannot exceed 2000 characters.'),
        ];
    }
}
