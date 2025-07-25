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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('Invoice_ID');
            $table->string('Invoice_Number')->unique(); // Auto-generated invoice number
            $table->unsignedBigInteger('PO_ID');
            $table->decimal('Invoice_Amount', 10, 2);
            $table->decimal('Tax', 10, 2)->default(0); // PPN field
            $table->enum('Payment_Method', ['Bank Transfer', 'Cash', 'Cheque', 'QRIS'])->nullable();
            $table->date('Due_Date');
            $table->enum('Payment_Status', ['Paid', 'Pending']);
            $table->date('Invoice_Date');
            $table->string('Attachment')->nullable(); // File upload
            $table->text('Notes')->nullable(); // Additional notes
            
            $table->timestamps();
    
            $table->foreign('PO_ID')->references('PO_ID')->on('purchase_orders');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};