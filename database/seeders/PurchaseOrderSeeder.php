<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\PurchaseRequisition;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $supplierId = Supplier::first()->Supplier_ID ?? 1;
        $employeeId = Employee::first()->Employee_ID ?? null;
        $requisitionId = PurchaseRequisition::first()->Requisition_ID ?? null;

        DB::table('purchase_orders')->insert([
            [
                'Supplier_ID' => $supplierId,
                'Requisition_ID' => $requisitionId,
                'Employee_ID' => $employeeId,
                'Order_Date' => Carbon::now()->subDays(3),
                'Expected_Delivery_Date' => Carbon::now()->addDays(7),
                'Item_Name' => 'A4 Paper',
                'Item_Description' => 'High-quality A4 white printing paper',
                'Item_Quantity' => 5000,
                'Item_Price' => 0.10,
                'Total_Amount' => 500.00,
                'Status' => 'Pending',
                'Notes' => 'Urgent delivery required.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Supplier_ID' => $supplierId,
                'Requisition_ID' => $requisitionId,
                'Employee_ID' => $employeeId,
                'Order_Date' => Carbon::now()->subDays(5),
                'Expected_Delivery_Date' => Carbon::now()->addDays(10),
                'Item_Name' => 'Printer Cartridge',
                'Item_Description' => 'Black ink cartridge for HP printers',
                'Item_Quantity' => 100,
                'Item_Price' => 15.00,
                'Total_Amount' => 1500.00,
                'Status' => 'Approved',
                'Notes' => 'Standard order',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
