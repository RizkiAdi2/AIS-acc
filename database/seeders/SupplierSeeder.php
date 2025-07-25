<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('suppliers')->insert([
        [
            'Name' => 'Global Paper Co.',
            'Contact_Email' => 'contact@globalpaper.com',
            'Phone_Number' => '+123456789',
            'Address' => '123 Paper Street, Cityville',
            'Supplier_Type' => 'Material',
            'Country' => 'USA',
            'State' => 'California',
            'Payment_Terms' => 'Net 30',
            'Product_Service_Type' => 'Product',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'Name' => 'Tech Supplies Ltd.',
            'Contact_Email' => 'sales@techsupplies.com',
            'Phone_Number' => '+987654321',
            'Address' => '456 Tech Road, Technopolis',
            'Supplier_Type' => 'Equipment',
            'Country' => 'Germany',
            'State' => 'Berlin',
            'Payment_Terms' => 'Net 15',
            'Product_Service_Type' => 'Product',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    }
}
