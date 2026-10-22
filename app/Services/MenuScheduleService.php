<?php

namespace App\Services;

use App\Models\MenuSchedule;
use Illuminate\Support\Facades\DB;

class MenuScheduleService
{
    public function getAll(array $filters = [])
    {
        $query = MenuSchedule::with([
            'menu:id,category_id,code,name,slug,price,min_pax',
            'menu.category:id,name',
        ]);

        if (!empty($filters['menu_id'])) {
            $query->where('menu_id', $filters['menu_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'schedule_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'schedule_date',
                '<=',
                $filters['date_to']
            );
        }

        if (
            array_key_exists('is_active', $filters)
            && $filters['is_active'] !== null
            && $filters['is_active'] !== ''
        ) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query
            ->orderBy('schedule_date')
            ->orderBy('menu_id')
            ->paginate(10)
            ->withQueryString();
    }

    public function findById(int $id): MenuSchedule
    {
        return MenuSchedule::with([
            'menu:id,category_id,code,name,slug,price,min_pax',
            'menu.category:id,name',
        ])->findOrFail($id);
    }

    public function create(array $data): MenuSchedule
    {
        $data['is_active'] = $data['is_active'] ?? true;

        $schedule = DB::transaction(function () use ($data) {
            return MenuSchedule::create($data);
        });

        return $schedule->load([
            'menu:id,category_id,code,name,slug,price,min_pax',
            'menu.category:id,name',
        ]);
    }

    public function update(
        MenuSchedule $schedule,
        array $data
    ): MenuSchedule {
        DB::transaction(function () use ($schedule, $data) {
            $schedule->update($data);
        });

        return $schedule->fresh()->load([
            'menu:id,category_id,code,name,slug,price,min_pax',
            'menu.category:id,name',
        ]);
    }

    public function delete(MenuSchedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            $schedule->delete();
        });
    }
}