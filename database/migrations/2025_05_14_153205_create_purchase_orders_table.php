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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('PO_ID');
            $table->unsignedBigInteger('Supplier_ID');
            $table->unsignedBigInteger('Requisition_ID')->nullable(); 
            $table->unsignedBigInteger('Employee_ID')->nullable();
            $table->date('Order_Date');  
            $table->date('Expected_Delivery_Date')->nullable();
            $table->string('Item_Name')->nullable();
            $table->text('Item_Description')->nullable();
            $table->integer('Item_Quantity')->nullable();
            $table->decimal('Item_Price', 15, 2)->nullable();
            $table->decimal('Total_Amount', 10, 2);
            $table->enum('Status', ['Pending', 'Completed', 'Approved']);
            $table->text('Notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('Supplier_ID')->references('Supplier_ID')->on('suppliers');
            $table->foreign('Requisition_ID')->references('Requisition_ID')->on('purchase_requisitions');
            $table->foreign('Employee_ID')->references('Employee_ID')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};