<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuScheduleRequest;
use App\Http\Requests\UpdateMenuScheduleRequest;
use App\Models\MenuSchedule;
use App\Services\MenuScheduleService;
use Illuminate\Http\Request;

class MenuScheduleController extends Controller
{
    public function __construct(
        private MenuScheduleService $scheduleService
    ) {}

    public function index(Request $request)
    {
        $schedules = $this->scheduleService->getAll(
            $request->only([
                'menu_id',
                'date_from',
                'date_to',
                'is_active',
            ])
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal menu berhasil diambil.',
            'data' => $schedules,
        ]);
    }

    public function store(StoreMenuScheduleRequest $request)
    {
        $schedule = $this->scheduleService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal menu berhasil ditambahkan.',
            'data' => $schedule,
        ], 201);
    }

    public function show(MenuSchedule $menuSchedule)
    {
        $schedule = $this->scheduleService->findById(
            $menuSchedule->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail jadwal menu berhasil diambil.',
            'data' => $schedule,
        ]);
    }

    public function update(
        UpdateMenuScheduleRequest $request,
        MenuSchedule $menuSchedule
    ) {
        $schedule = $this->scheduleService->update(
            $menuSchedule,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal menu berhasil diperbarui.',
            'data' => $schedule,
        ]);
    }

    public function destroy(MenuSchedule $menuSchedule)
    {
        $this->scheduleService->delete($menuSchedule);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal menu berhasil dihapus.',
        ]);
    }
}
