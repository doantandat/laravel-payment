<?php

namespace Datdoan\LaravelPayment\Services;

use Datdoan\LaravelPayment\Contracts\PaymentGatewayInterface;

class VNPayService implements PaymentGatewayInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createPaymentUrl(array $params): string
    {
        // Các trường bắt buộc từ phía merchant
        $required = ['order_id', 'order_desc', 'amount'];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                throw new \InvalidArgumentException("Thiếu tham số bắt buộc: {$field}");
            }
        }

        $vnp_Url        = $this->config['vnp_url'];
        $vnp_TmnCode    = $this->config['vnp_tmn_code'];
        $vnp_HashSecret = $this->config['vnp_hash_secret'];
        $vnp_Returnurl  = $this->config['vnp_return_url'];

        $vnp_TxnRef     = $params['order_id'];
        $vnp_OrderInfo  = $params['order_desc'];
        $vnp_OrderType  = $params['order_type'] ?? 'other';
        $vnp_Amount     = $params['amount'] * 100; // VNPay yêu cầu nhân 100
        $vnp_Locale     = $params['locale'] ?? 'vn';
        $vnp_BankCode   = $params['bank_code'] ?? null;
        $vnp_IpAddr     = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        // Thời gian tạo giao dịch
        $vnp_CreateDate = date('YmdHis');
        // Thời gian hết hạn (mặc định +15 phút)
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_Command"    => "pay",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => $vnp_Amount,
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_ExpireDate" => $vnp_ExpireDate,
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $vnp_IpAddr,
            "vnp_Locale"     => $vnp_Locale,
            "vnp_OrderInfo"  => $vnp_OrderInfo,
            "vnp_OrderType"  => $vnp_OrderType,
            "vnp_ReturnUrl"  => $vnp_Returnurl,
            "vnp_TxnRef"     => $vnp_TxnRef,
        ];

        if ($vnp_BankCode) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query    = http_build_query($inputData);
        $hashdata = urldecode($query);

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        return $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;
    }


    public function verifyPayment(array $input): bool
    {
        $vnp_HashSecret = $this->config['vnp_hash_secret'];
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? '';

        unset($input['vnp_SecureHashType'], $input['vnp_SecureHash']);
        ksort($input);

        $hashData = urldecode(http_build_query($input));
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        return $secureHash === $vnp_SecureHash;
    }
}
