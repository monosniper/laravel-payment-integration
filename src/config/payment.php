<?php

return [
    'api_prefix' => 'api/v1',

    'vat_percent' => 12,
    'inn' => '',

    'click' => [
        'merchant_id' => '',
        'merchant_user_id' => '',
        'service_id' => '',
        'secret_key' => '',
        'with_split' => false,

        'enabled' => false,
    ],

    'payme' => [
        'merchant_id' => '',
        'key' => '',
        'test_key' => '',
        'parameter' => 'order_id',

        'is_test' => false,
        'enabled' => false,
    ],

    'uzum' => [
        'terminal_id' => '',
        'api_key' => '',

        'test' => [
            'terminal_id' => '',
            'api_key' => '',
        ],

        'is_test' => true,
        'enabled' => false,
    ],

    'quickpay' => [
        'shop_id' => '',
        'secret_key' => '',

        'enabled' => false,
    ],

    'infinitypay' => [
        'vendor_id' => '',
        'secret_key' => '',

        'is_test' => true,
        'enabled' => false,
    ],

    'octobank' => [
        'shop_id' => '',
        'secret' => '',
        'hash_secret' => '',

        'is_test' => true,
        'enabled' => false,
    ],
];
