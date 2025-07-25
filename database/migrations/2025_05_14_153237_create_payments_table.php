<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('Payment_ID');
            $table->unsignedBigInteger('Invoice_ID');
            $table->decimal('Amount_Paid', 10, 2);
            $table->date('Payment_Date');
            $table->enum('Payment_Method', ['Bank Transfer', 'Check', 'Cash']);
            $table->timestamps();
    
            $table->foreign('Invoice_ID')->references('Invoice_ID')->on('invoices');
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
