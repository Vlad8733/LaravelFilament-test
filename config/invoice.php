<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Information for Invoices
    |--------------------------------------------------------------------------
    |
    | This information will be displayed on generated invoices.
    |
    */

    'company' => [
        'name' => env('INVOICE_COMPANY_NAME', 'e-Shop'),
        'address' => env('INVOICE_COMPANY_ADDRESS', '123 Commerce Street'),
        'city' => env('INVOICE_COMPANY_CITY', 'Business City, BC 12345'),
        'country' => env('INVOICE_COMPANY_COUNTRY', 'United States'),
        'phone' => env('INVOICE_COMPANY_PHONE', '+1 (555) 123-4567'),
        'email' => env('INVOICE_COMPANY_EMAIL', 'support@e-shop.com'),
        'website' => env('INVOICE_COMPANY_WEBSITE', 'www.e-shop.com'),
        'logo' => env('INVOICE_COMPANY_LOGO', null), // Path to logo image
        'vat_number' => env('INVOICE_COMPANY_VAT', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Settings
    |--------------------------------------------------------------------------
    */

    'pdf' => [
        'paper' => 'a4',
        'orientation' => 'portrait',
    ],
];
