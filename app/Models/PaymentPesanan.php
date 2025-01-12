<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentPesanan extends Model
{
    use HasFactory;

    protected $table = 'payment_pesanan';

    protected $fillable = [
        'pesanan_id',
        'payment_method', // pastikan sesuai dengan nama kolom
        'amount', // mengganti 'payment_amount' dengan 'amount'
        'transaction_id',
        'status', // mengganti 'payment_status' dengan 'status'
        'payment_url'
    ];
}
