<?php

namespace Datdoan\LaravelPayment\Contracts;

interface PaymentGatewayInterface
{
    public function createPaymentUrl(array $params): string;
    public function verifyPayment(array $input): bool;
}
