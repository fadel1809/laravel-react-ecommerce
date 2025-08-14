<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('departments')->insert([
            [
                'name' => 'Electronics',
                'slug' => Str::slug('Electronics'),
                'meta_title' => 'Best Electronics',
                'meta_description' => 'Find the latest and best electronic products',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fashion',
                'slug' => Str::slug('Fashion'),
                'meta_title' => 'Trendy Fashion',
                'meta_description' => 'Stay stylish with the latest fashion trends',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Home & Living',
                'slug' => Str::slug('Home & Living'),
                'meta_title' => 'Home and Living Essentials',
                'meta_description' => 'Everything you need for your home and lifestyle',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

