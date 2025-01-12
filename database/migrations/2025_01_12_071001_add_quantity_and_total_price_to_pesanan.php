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
            $table->integer('quantity')->default(1);  // Kolom quantity
            $table->decimal('total_price', 8, 2)->default(0);  // Kolom total_price
        });
    }
    
    public function down()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('quantity');
            $table->dropColumn('total_price');
        });
    }
    

};
