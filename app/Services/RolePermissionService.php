<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RolePermissionService
{
    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    public function assignPermissions(
        Role $role,
        array $permissionIds
    ): Role {
        $permissions = Permission::whereIn(
            'id',
            $permissionIds
        )->pluck('id')->all();

        if (count(array_unique($permissionIds)) !== count($permissions)) {
            throw ValidationException::withMessages([
                'permission_ids' => [
                    'Terdapat permission yang tidak ditemukan.',
                ],
            ]);
        }

        DB::transaction(function () use ($role, $permissions) {
            $role->permissions()->sync($permissions);
        });

        return $role->load('permissions');
    }
}