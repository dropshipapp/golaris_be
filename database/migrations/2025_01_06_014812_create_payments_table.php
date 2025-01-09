<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
{
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
        $table->string('order_id')->unique();
        $table->decimal('gross_amount', 15, 2);
        $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
        $table->timestamps();
    });
}


    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
