<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RolesEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{

    public function run(): void
    {
        $userRole = Role::create(['name' => RolesEnum::User->value]);
        $vendorRole = Role::create(['name' => RolesEnum::Vendor->value]);
        $adminRole = Role::create(['name' => RolesEnum::Admin->value]);

        $approveVendorsPermission = Permission::create(['name' => PermissionEnum::ApproveVendors->value]);
        $sellProducts = Permission::create(['name' => PermissionEnum::SellProducts->value]);
        $buyProducts = Permission::create(['name' => PermissionEnum::BuyProducts->value]);    

        $userRole->syncPermissions([$buyProducts]);
        $vendorRole->syncPermissions([$sellProducts,$buyProducts]);
        $adminRole->syncPermissions([$approveVendorsPermission, $sellProducts, $buyProducts]);
    }
}
