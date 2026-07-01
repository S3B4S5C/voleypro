<?php

return [
    'base_url' => env('PAGOFACIL_BASE_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2'),

    'commerce_id' => env('PAGOFACIL_COMMERCE_ID'),
    'token_service' => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret' => env('PAGOFACIL_TOKEN_SECRET'),

    'payment_method' => env('PAGOFACIL_PAYMENT_METHOD'),
    'currency' => (int) env('PAGOFACIL_CURRENCY', 2),

    'callback_url' => env('PAGOFACIL_CALLBACK_URL'),

    'verify_ssl' => filter_var(env('PAGOFACIL_VERIFY_SSL', true), FILTER_VALIDATE_BOOLEAN),

    'status' => [
        'pending' => env('PAGOFACIL_STATUS_PENDING', '1'),
        'paid' => env('PAGOFACIL_STATUS_PAID', '2'),
        'review' => env('PAGOFACIL_STATUS_REVIEW', '5'),
    ],
];
