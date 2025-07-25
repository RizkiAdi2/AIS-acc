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
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id('Supplier_ID');
        $table->string('Name');
        $table->string('Contact_Email');
        $table->string('Phone_Number', 50);
        $table->string('Address');
        $table->string('Supplier_Type'); 
        $table->string('Country');       
        $table->string('State'); 
        $table->string('Payment_Terms', 100);
        $table->string('Product_Service_Type');
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
