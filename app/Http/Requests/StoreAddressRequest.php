<?php

namespace App\Http\Requests;

use App\Models\UserAddress;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:2'],
            'is_default' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => __('Please select an address label.'),
            'full_name.required' => __('Please enter the recipient name.'),
            'phone.required' => __('Please enter a phone number.'),
            'address_line_1.required' => __('Please enter the street address.'),
            'city.required' => __('Please enter the city.'),
            'postal_code.required' => __('Please enter the postal code.'),
            'country.required' => __('Please select the country.'),
        ];
    }

    public static function countryOptions(): array
    {
        return UserAddress::countryOptions();
    }
}
