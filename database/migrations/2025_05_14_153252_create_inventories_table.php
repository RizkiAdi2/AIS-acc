<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id('Material_ID');
            $table->string('Material_Name');
            $table->string('Material_Description');
            $table->string('Material_Type'); 
            $table->integer('Quantity_Available');
            $table->string('Unit_Of_Measurement'); 
            $table->decimal('Unit_Cost', 10, 2);
            $table->integer('Reorder_Level');
            $table->string('Location'); 

            // Supplier foreign key
            $table->unsignedBigInteger('Supplier_ID');
            $table->foreign('Supplier_ID')->references('Supplier_ID')->on('suppliers');

            // Diskon
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable(); // Jenis diskon
            $table->decimal('discount_value', 10, 2)->nullable(); // Nilai diskon
            $table->decimal('total_cost_after_discount', 12, 2)->nullable(); // Total setelah diskon

            // Informasi karyawan
            $table->unsignedBigInteger('employee_id')->nullable();

            // Pembayaran
            $table->enum('payment_method', ['Bank Transfer', 'Cash', 'Dana', 'OVO', 'Gopay'])->nullable();
            $table->enum('payment_term', ['Cash on Delivery', 'Net 30', 'Net 90', 'Prepayment'])->nullable();
            $table->date('payment_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
