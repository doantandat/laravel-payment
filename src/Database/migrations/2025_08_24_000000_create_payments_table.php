<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Cổng thanh toán: vnpay, momo, zalopay, stripe, paypal...
            $table->string('gateway')->index();

            // ID đơn hàng trong hệ thống của bạn
            $table->string('order_id')->index();

            // Mã giao dịch bên cổng thanh toán (TransactionNo / TransId...)
            $table->string('transaction_no')->nullable()->index();

            // Số tiền
            $table->bigInteger('amount')->default(0);

            // Loại tiền tệ (VND, USD, ...)
            $table->string('currency', 10)->default('VND');

            // Mã ngân hàng / phương thức thanh toán (BankCode, Wallet, Card...)
            $table->string('payment_method')->nullable();

            // Mã phản hồi (00 thành công, hoặc tương ứng từng gateway)
            $table->string('response_code')->nullable();

            // Trạng thái giao dịch (success, pending, failed...)
            $table->string('transaction_status')->nullable();

            // Chữ ký bảo mật (secure hash / signature)
            $table->string('secure_hash')->nullable();

            // Dữ liệu bổ sung (ví dụ user_id, metadata)
            $table->json('meta')->nullable();

            // Lưu toàn bộ response gốc trả về từ gateway
            $table->json('raw_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

