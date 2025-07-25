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
    Schema::create('requisition_items', function (Blueprint $table) {
        $table->id('Requisition_Item_ID');
        $table->unsignedBigInteger('Requisition_ID');
        $table->unsignedBigInteger('Material_ID');
        $table->integer('Quantity_Requested');
        $table->decimal('Unit_Price', 10, 2);
        $table->timestamps();

        $table->foreign('Requisition_ID')->references('Requisition_ID')->on('purchase_requisitions');
        $table->foreign('Material_ID')->references('Material_ID')->on('inventories');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
