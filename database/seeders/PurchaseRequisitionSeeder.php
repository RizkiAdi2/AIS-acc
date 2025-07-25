<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PurchaseRequisitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employeeId = DB::table('employees')->first()?->Employee_ID ?? 1;
        $supplierId = DB::table('suppliers')->first()?->Supplier_ID;

        DB::table('purchase_requisitions')->insert([
            [
                'Employee_ID' => $employeeId,
                'Supplier_ID' => $supplierId,
                'Department' => 'Procurement',
                'Date_Requested' => Carbon::now()->subDays(5),
                'Status' => 'Pending',
                'Description' => 'Need A4 paper for office use.',
                'Item_Name' => 'A4 Paper',
                'Item_Quantity' => 1000,
                'Item_Price' => 0.1,
                'Total_Cost' => 100.00, // Optional if auto-calculated
                'Expected_Delivery_Date' => Carbon::now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Employee_ID' => $employeeId,
                'Supplier_ID' => $supplierId,
                'Department' => 'Marketing',
                'Date_Requested' => Carbon::now()->subDays(2),
                'Status' => 'Approved',
                'Description' => 'Brochures for upcoming event.',
                'Item_Name' => 'Marketing Brochures',
                'Item_Quantity' => 500,
                'Item_Price' => 0.75,
                'Total_Cost' => 375.00,
                'Expected_Delivery_Date' => Carbon::now()->addDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
