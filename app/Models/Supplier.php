<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    // Kolom yang dapat diisi (mass assignable).
    protected $fillable = ['name', 'email', 'password', 'payment_status'];

    /**
     * Relasi ke model Payment (Setiap supplier dapat memiliki banyak pembayaran).
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
