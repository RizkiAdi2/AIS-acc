<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supplierId = Supplier::first()->Supplier_ID ?? 1;

        DB::table('inventories')->insert([
            [
                'Material_Name' => 'A4 Paper',
                'Material_Description' => 'Standard A4 white printing paper',
                'Material_Type' => 'Raw Material',
                'Quantity_Available' => 5000,
                'Unit_Of_Measurement' => 'piece',
                'Unit_Cost' => 0.10,
                'Reorder_Level' => 1000,
                'Location' => 'Warehouse A',
                'Supplier_ID' => $supplierId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Material_Name' => 'Printer Cartridge',
                'Material_Description' => 'Black ink cartridge for HP printers',
                'Material_Type' => 'Consumables',
                'Quantity_Available' => 200,
                'Unit_Of_Measurement' => 'piece',
                'Unit_Cost' => 15.00,
                'Reorder_Level' => 50,
                'Location' => 'Warehouse B',
                'Supplier_ID' => $supplierId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Material_Name' => 'Printed Brochures',
                'Material_Description' => 'Company marketing brochures',
                'Material_Type' => 'Finished Goods',
                'Quantity_Available' => 1000,
                'Unit_Of_Measurement' => 'piece',
                'Unit_Cost' => 0.75,
                'Reorder_Level' => 200,
                'Location' => 'Warehouse C',
                'Supplier_ID' => $supplierId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
