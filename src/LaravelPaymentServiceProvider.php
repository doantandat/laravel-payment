<?php

namespace Datdoan\LaravelPayment;

use Illuminate\Support\ServiceProvider;
use Datdoan\LaravelPayment\Services\VNPayService;

class LaravelPaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-payment.php', 'laravel-payment');

        $this->app->singleton('laravel-payment.vnpay', function ($app) {
            return new VNPayService(config('laravel-payment.vnpay'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-payment.php' => config_path('laravel-payment.php'),
        ], 'config');
    }
}
