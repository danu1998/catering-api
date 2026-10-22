<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'dashboard.view', 'display_name' => 'Lihat Dashboard', 'module' => 'dashboard'],
            ['name' => 'menu.view', 'display_name' => 'Lihat Menu', 'module' => 'menu'],
            ['name' => 'menu.create', 'display_name' => 'Tambah Menu', 'module' => 'menu'],
            ['name' => 'menu.update', 'display_name' => 'Ubah Menu', 'module' => 'menu'],
            ['name' => 'menu.delete', 'display_name' => 'Hapus Menu', 'module' => 'menu'],
            ['name' => 'order.view', 'display_name' => 'Lihat Pesanan', 'module' => 'order'],
            ['name' => 'order.update', 'display_name' => 'Ubah Pesanan', 'module' => 'order'],
            ['name' => 'payment.view', 'display_name' => 'Lihat Pembayaran', 'module' => 'payment'],
            ['name' => 'payment.update', 'display_name' => 'Kelola Pembayaran', 'module' => 'payment'],
            ['name' => 'user.manage', 'display_name' => 'Kelola Pengguna', 'module' => 'user'],
            [
                'name' => 'category.view',
                'display_name' => 'Lihat Kategori',
                'module' => 'category',
            ],
            [
                'name' => 'category.create',
                'display_name' => 'Tambah Kategori',
                'module' => 'category',
            ],
            [
                'name' => 'category.update',
                'display_name' => 'Ubah Kategori',
                'module' => 'category',
            ],
            [
                'name' => 'category.delete',
                'display_name' => 'Hapus Kategori',
                'module' => 'category',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $superAdmin = Role::updateOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh ke seluruh modul.',
                'is_active' => true,
            ]
        );

        $admin = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'Administrator',
                'description' => 'Mengelola operasional catering.',
                'is_active' => true,
            ]
        );

        $staff = Role::updateOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Staff',
                'description' => 'Akses operasional berdasarkan permission.',
                'is_active' => true,
            ]
        );

        $superAdmin->permissions()->sync(
            Permission::pluck('id')->all()
        );

        $admin->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'menu.view',
                'menu.create',
                'menu.update',
                'order.view',
                'order.update',
                'payment.view',
                'payment.update',
                'category.view',
                'category.create',
                'category.update',
                'category.delete',
                'menu.view',
                'menu.create',
                'menu.update',
                'menu.delete',
                'variant.view',
                'variant.create',
                'variant.update',
                'variant.delete',
                'menu_schedule.view',
                'menu_schedule.create',
                'menu_schedule.update',
                'menu_schedule.delete',
                'inventory.view',
                'inventory.create',
                'inventory.update',
                'inventory.delete',
                'inventory_mutation.view',
                'inventory_mutation.create',
                'inventory_mutation.update',
                'inventory_mutation.delete',
                'stock_opname.view',
                'stock_opname.create',
                'stock_opname.update',
                'stock_opname.complete',
                'stock_opname.cancel',
                'transaction.view',
                'transaction.create',
                'transaction.update',
                'transaction.status_update',
                'payment_method.view',
                'payment_method.create',
                'payment_method.update',
                'transaction_payment.view',
                'transaction_payment.create',
                'transaction_payment.status_update',
                'customer.view',
                'customer.create',
                'customer.update',
                'expense_category.view',
                'expense_category.create',
                'expense_category.update',
                'expense_category.delete',
                'expense.view',
                'expense.create',
                'expense.update',
                'expense.delete',
                'review.view',
                'review.create',
                'review.update',
                'review.delete',
                'analytics.view',
                'report.view',
                'business_profile.view',
                'business_profile.update'
            ])->pluck('id')->all()
        );

        $staff->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'menu.view',
                'order.view',
            ])->pluck('id')->all()
        );

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (!$email || !$password) {
            throw new RuntimeException(
                'ADMIN_EMAIL dan ADMIN_PASSWORD harus diatur di .env.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($password),
                'role_id' => $superAdmin->id,
            ]
        );
    }
}
