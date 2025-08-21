<?php

use Datdoan\LaravelPayment\Services\VNPayService;
use PHPUnit\Framework\TestCase;

class VNPayServiceTest extends TestCase
{
    protected $config = [
        'vnp_url'        => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
        'vnp_tmn_code'   => 'TESTCODE',
        'vnp_hash_secret'=> 'TESTSECRET',
        'vnp_return_url' => 'http://localhost/return',
    ];

    public function testCreatePaymentUrlSuccess()
    {
        $service = new VNPayService($this->config);

        $url = $service->createPaymentUrl([
            'order_id'   => 123,
            'order_desc' => 'Thanh toan test',
            'amount'     => 100000,
        ]);

        $this->assertStringContainsString('vnp_TxnRef=123', $url);
        $this->assertStringContainsString('vnp_SecureHash=', $url);
    }

    public function testMissingRequiredParams()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = new VNPayService($this->config);

        // Thiếu order_desc -> phải ném exception
        $service->createPaymentUrl([
            'order_id' => 123,
            'amount'   => 100000,
        ]);
    }
}
