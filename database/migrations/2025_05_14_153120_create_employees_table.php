<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create employees table
        Schema::create('employees', function (Blueprint $table) {
            $table->id('Employee_ID'); // Primary key
            $table->string('Name', 255);
            $table->string('Department', 100);
            $table->string('Role', 100);
            $table->string('Phone', 15)->nullable();
            $table->string('Email', 255)->unique()->nullable();
            $table->text('Address')->nullable();

            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->decimal('salary', 15, 2)->nullable();
            $table->string('identification_number', 20)->nullable();

            $table->date('hire_date')->nullable(); // ✅ gunakan snake_case
            $table->enum('Status', ['Active', 'Inactive', 'On Leave', 'Sick'])->default('Active');

            $table->timestamps(); // created_at & updated_at
        });

        // Add foreign key to inventories only if it exists
        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                if (!Schema::hasColumn('inventories', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable();

                    $table->foreign('employee_id')
                        ->references('Employee_ID')
                        ->on('employees')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down(): void
    {
        // Safely drop foreign key and column only if table exists
        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                if (Schema::hasColumn('inventories', 'employee_id')) {
                    $table->dropForeign(['employee_id']);
                    $table->dropColumn('employee_id');
                }
            });
        }

        // Drop employees table
        Schema::dropIfExists('employees');
    }
};
