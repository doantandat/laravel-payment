<?php
namespace Datdoan\LaravelPayment\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'gateway',
        'order_id',
        'transaction_no',
        'amount',
        'bank_code',
        'response_code',
        'transaction_status',
        'secure_hash',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
    ];
}


?>