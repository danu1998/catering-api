<?php

namespace App\Services;

use App\Models\BusinessProfile;
use Illuminate\Support\Facades\DB;

class BusinessProfileService
{
    public function getPublicProfile(): ?BusinessProfile
    {
        return BusinessProfile::query()
            ->where('is_active', true)
            ->first();
    }

    public function getAdminProfile(): ?BusinessProfile
    {
        return BusinessProfile::query()->first();
    }

    public function save(array $data): BusinessProfile
    {
        return DB::transaction(function () use ($data) {
            $profile = BusinessProfile::query()->first();

            if ($profile) {
                $profile->fill($data);
                $profile->save();

                return $profile->fresh();
            }

            return BusinessProfile::query()->create(
                array_merge(
                    $data,
                    ['is_active' => $data['is_active'] ?? true]
                )
            );
        });
    }
}