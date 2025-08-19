<?php

namespace Datdoan\LaravelPayment\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelPayment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-payment.vnpay';
    }
}
