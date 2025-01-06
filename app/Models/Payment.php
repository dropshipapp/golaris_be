<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    /**
     * Kolom yang bisa diisi (mass assignable).
     */
    protected $fillable = [
        'order_id',
        'supplier_id',
        'gross_amount',
        'payment_status',
    ];

    /**
     * Relasi ke model Supplier (Setiap pembayaran dimiliki oleh satu supplier).
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
