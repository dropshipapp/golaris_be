<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentPesananTable extends Migration
{
    public function up()
{
    Schema::create('payment-pesanan', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('pesanan_id');
        $table->enum('payment_status', ['pending', 'settlement', 'failed']);
        $table->decimal('payment_amount', 10, 2);
        $table->string('payment_method');
        $table->timestamp('payment_date')->nullable();
        $table->timestamps();

        // Menambahkan foreign key constraint
        $table->foreign('pesanan_id')->references('id')->on('pesanan')->onDelete('cascade');
    });
}


    public function down()
    {
        Schema::dropIfExists('payment_pesanan');
    }
}
