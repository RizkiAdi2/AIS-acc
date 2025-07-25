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
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id('Requisition_ID'); // Primary key, sesuai dengan model
            $table->unsignedBigInteger('Employee_ID');
            $table->unsignedBigInteger('Supplier_ID')->nullable(); // Supplier ID untuk pembuatan PO otomatis
            $table->string('Department', 100);
            $table->date('Date_Requested');
            $table->enum('Status', ['Pending', 'Approved', 'Rejected']);
            $table->text('Description')->nullable(); // Deskripsi barang yang dibutuhkan
            $table->string('Item_Name'); // Nama barang yang dibutuhkan
            $table->integer('Item_Quantity'); // Jumlah barang yang dibutuhkan
            $table->decimal('Item_Price', 10, 2); // Harga per unit barang
            $table->decimal('Total_Cost', 10, 2)->nullable(); // Total biaya (akan dihitung otomatis di model)
            $table->date('Expected_Delivery_Date'); // Tanggal pengiriman yang diinginkan
            $table->timestamps();
            $table->softDeletes(); // Dukungan penghapusan lembut

            // Menambahkan relasi ke tabel employees
            $table->foreign('Employee_ID')->references('Employee_ID')->on('employees')->onDelete('cascade');
            
            // Menambahkan relasi ke tabel suppliers
            $table->foreign('Supplier_ID')->references('Supplier_ID')->on('suppliers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};