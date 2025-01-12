<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesananTable extends Migration
{
    public function up()
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('product_id'); // Menambahkan kolom product_id
            $table->decimal('price', 10, 2); // Menambahkan kolom price
            $table->date('order_date');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
    
            // Foreign key untuk supplier
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            
            // Foreign key untuk product
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
    

    public function down()
    {
        Schema::dropIfExists('pesanan');
    }
}
