<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'image_url',
        'category_id', // Tambahkan ini
        'supplier_id', // Pastikan kolom ini bisa diisi

    ];

    // Relasi ke kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Di dalam model Product
    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : null;
    }


    public function supplier()
    {
        return $this->belongsTo(User::class, 'supplier_id');
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class);
    }
}
