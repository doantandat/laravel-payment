<?php

return [
    'vnpay' => [
        'vnp_url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'vnp_tmn_code' => env('VNP_TMN_CODE', ''),
        'vnp_hash_secret' => env('VNP_HASH_SECRET', ''),
        'vnp_return_url' => env('VNP_RETURN_URL', 'http://localhost/payment/vnpay-return'),
    ],
];
