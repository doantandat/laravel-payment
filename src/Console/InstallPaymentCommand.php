<?php 
namespace Datdoan\LaravelPayment\Console;

use Illuminate\Console\Command;

class InstallPaymentCommand extends Command
{
    protected $signature = 'laravel-payment:install';
    protected $description = 'Cài đặt Laravel Payment (tạo bảng payments và publish config)';

    public function handle()
    {
        $this->info('Đang publish config...');
        $this->call('vendor:publish', [
            '--provider' => "Datdoan\LaravelPayment\Providers\LaravelPaymentServiceProvider",
            '--tag'      => 'config'
        ]);

        $this->info('Đang migrate bảng payments...');

        // Lấy đúng đường dẫn migration trong package
        $migrationPath = 'vendor/datdoan/laravel-payment/src/Database/migrations/2025_08_24_000000_create_payments_table.php';

        $this->call('migrate', [
            '--path' => $migrationPath,
        ]);

        $this->info('Hoàn tất cài đặt Laravel Payment!');
    }
}


?>