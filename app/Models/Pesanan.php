<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;

    // Tentukan nama tabel secara eksplisit
    protected $table = 'pesanan'; // Pastikan ini sesuai dengan nama tabel yang benar

    protected $fillable = ['supplier_id', 'product_id', 'quantity', 'price', 'total_price', 'order_date', 'status'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Set price saat membuat pesanan, berdasarkan harga produk dan quantity
    public static function createPesanan($supplier_id, $product_id, $quantity, $order_date, $status = 'pending')
    {
        $product = Product::find($product_id); // Menemukan produk berdasarkan ID
        $price = $product->price; // Mendapatkan harga produk

        // Menghitung total harga berdasarkan kuantitas
        $totalPrice = $price * $quantity;

        return self::create([
            'supplier_id' => $supplier_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $price,
            'total_price' => $totalPrice, // Menyimpan total harga
            'order_date' => $order_date,
            'status' => $status, // Menyimpan status pesanan (optional, default 'pending')
        ]);
    }
}
