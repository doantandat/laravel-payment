<?php

namespace Datdoan\LaravelPayment\Services;

use Datdoan\LaravelPayment\Contracts\PaymentGatewayInterface;
use Datdoan\LaravelPayment\Models\Payment;
use Illuminate\Http\Request;

class VNPayService implements PaymentGatewayInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        // đảm bảo timezone đúng
        date_default_timezone_set('Asia/Ho_Chi_Minh');
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

        // build inputData giống demo
        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => $vnp_Amount,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $vnp_IpAddr,
            "vnp_Locale"     => $vnp_Locale,
            "vnp_OrderInfo"  => $vnp_OrderInfo,
            "vnp_OrderType"  => $vnp_OrderType,
            "vnp_ReturnUrl"  => $vnp_Returnurl,
            "vnp_TxnRef"     => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate,
        ];

        if ($vnp_BankCode) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);

        // build query + hashdata giống code mẫu
        $hashdata = '';
        $query = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        return $vnp_Url . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash;
    }

    public function verifyPayment(array $input): bool
    {
        $vnp_HashSecret = $this->config['vnp_hash_secret'];
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? '';

        unset($input['vnp_SecureHashType'], $input['vnp_SecureHash']);
        ksort($input);

        $hashData = '';
        $i = 0;
        foreach ($input as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        return $secureHash === $vnp_SecureHash;
    }


    public function handleReturn(Request $request)
    {
        $input = $request->all();
        $isVerified = $this->verifyPayment($input);

        if (!$isVerified) {
            return false; // chữ ký sai
        }

        return Payment::create([
            'gateway'            => 'vnpay',

            // order_id từ hệ thống của bạn (tham số gửi sang VNPay)
            'order_id'           => $input['vnp_TxnRef'] ?? '',

            // mã giao dịch phía VNPay
            'transaction_no'     => $input['vnp_TransactionNo'] ?? null,

            // số tiền (VNPay trả về nhân 100)
            'amount'             => isset($input['vnp_Amount']) ? (int) $input['vnp_Amount'] / 100 : 0,

            // loại tiền tệ
            'currency'           => 'VND',

            // phương thức thanh toán (bank code)
            'payment_method'     => $input['vnp_BankCode'] ?? null,

            // mã phản hồi (00 = thành công)
            'response_code'      => $input['vnp_ResponseCode'] ?? null,

            // trạng thái giao dịch (chuẩn hóa)
            'transaction_status' => ($input['vnp_ResponseCode'] ?? '') === '00' ? 'success' : 'failed',

            // chữ ký bảo mật
            'secure_hash'        => $input['vnp_SecureHash'] ?? null,

            // meta bổ sung: locale, order_info...
            'meta'               => [
                'locale'     => $input['vnp_Locale'] ?? null,
                'order_info' => $input['vnp_OrderInfo'] ?? null,
                'pay_date'   => $input['vnp_PayDate'] ?? null,
            ],

            // lưu toàn bộ dữ liệu gốc
            'raw_data'           => $input,
        ]);
    }
}
