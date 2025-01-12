<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('pesanan', function (Blueprint $table) {
        $table->string('payment_url')->nullable(); // Menambahkan kolom payment_url
    });
}

public function down()
{
    Schema::table('pesanan', function (Blueprint $table) {
        $table->dropColumn('payment_url'); // Menghapus kolom payment_url jika rollback
    });
}

};
