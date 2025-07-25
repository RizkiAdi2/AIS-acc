<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('employees')->insert([
            [
                'Name' => 'Beri',
                'Department' => 'Information Technology',
                'Role' => 'Manager',
                'Phone' => '1234567890',
                'Email' => 'beri@example.com',
                'Address' => '123 Finance Street',
                'Hire_Date' => '2022-05-01',
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Name' => 'vincent',
                'Department' => 'Inventory',
                'Role' => 'Supervisor',
                'Phone' => '0987654321',
                'Email' => 'vincent@example.com',
                'Address' => '456 HR Ave',
                'Hire_Date' => '2021-08-15',
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'Name' => 'rizki',
                'Department' => 'Production',
                'Role' => 'Director',
                'Phone' => '1122334455',
                'Email' => 'rizki@example.com',
                'Address' => '789 IT Blvd',
                'Hire_Date' => '2021-08-15',
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'Name' => 'kadek',
                'Department' => 'Finance',
                'Role' => 'Manager',
                'Phone' => '1234567890',
                'Email' => 'kadek@example.com',
                'Address' => '123 Finance Street',
                'Hire_Date' => '2022-05-01',
                'Status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
