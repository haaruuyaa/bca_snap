<?php

return [
    'bank' => [
        'url' => env('BCA_API_URL'),
        'port' => env('BCA_BANK_API_PORT'),
        'client' => env('BCA_BANK_CLIENT_ID'),
        'secret' => env('BCA_BANK_CLIENT_SECRET'),
        'channel' => env('BCA_BANK_CHANNEL_ID'),
        'partner' => env('BCA_BANK_PARTNER_ID')
    ],
    'va' => [
        'url' => env('BCA_API_URL'),
        'port' => env('BCA_VA_API_PORT'),
        'client' => env('BCA_VA_CLIENT_ID'),
        'secret' => env('BCA_VA_CLIENT_SECRET'),
        'channel' => env('BCA_VA_CHANNEL_ID'),
        'partner' => env('BCA_VA_PARTNER_ID')
    ]
];
