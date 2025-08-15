<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            // Electronics (department_id = 1)
            [
                'name' => 'Electronics',
                'department_id' => 1,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smartphones',
                'department_id' => 1,
                'parent_id' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Laptops',
                'department_id' => 1,
                'parent_id' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Android',
                'department_id' => 1,
                'parent_id' => 2, // Smartphones
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'iOS',
                'department_id' => 1,
                'parent_id' => 2, // Smartphones
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gaming Laptops',
                'department_id' => 1,
                'parent_id' => 3, // Laptops
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ultrabooks',
                'department_id' => 1,
                'parent_id' => 3, // Laptops
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Fashion (department_id = 2)
            [
                'name' => 'Fashion',
                'department_id' => 2,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Men Clothing',
                'department_id' => 2,
                'parent_id' => 8,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Women Clothing',
                'department_id' => 2,
                'parent_id' => 8,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shirts',
                'department_id' => 2,
                'parent_id' => 9, // Men Clothing
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pants',
                'department_id' => 2,
                'parent_id' => 9, // Men Clothing
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dresses',
                'department_id' => 2,
                'parent_id' => 10, // Women Clothing
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Skirts',
                'department_id' => 2,
                'parent_id' => 10, // Women Clothing
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Home Living (department_id = 3)
            [
                'name' => 'Furniture',
                'department_id' => 3,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kitchenware',
                'department_id' => 3,
                'parent_id' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sofas',
                'department_id' => 3,
                'parent_id' => 15, // Furniture
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tables',
                'department_id' => 3,
                'parent_id' => 15, // Furniture
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cookware',
                'department_id' => 3,
                'parent_id' => 16, // Kitchenware
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Dinnerware',
                'department_id' => 3,
                'parent_id' => 16, // Kitchenware
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
