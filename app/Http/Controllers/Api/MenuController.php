<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use App\Services\MenuService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function index(Request $request)
    {
        $menus = $this->menuService->getAll(
            $request->only([
                'search',
                'category_id',
                'is_active',
            ])
        );

        $menus->getCollection()->each(function ($menu) {
            $menu->append('image_url');
        });

        return response()->json([
            'success' => true,
            'message' => 'Data menu berhasil diambil.',
            'data' => $menus,
        ]);
    }

    public function store(StoreMenuRequest $request)
    {
        $menu = $this->menuService->create(
            $request->validated()
        );

        $menu->append('image_url');

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil ditambahkan.',
            'data' => $menu,
        ], 201);
    }

    public function show(Menu $menu)
    {
        $menu = $this->menuService->findById($menu->id);
        $menu->append('image_url');

        return response()->json([
            'success' => true,
            'message' => 'Detail menu berhasil diambil.',
            'data' => $menu,
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu)
    {
        $menu = $this->menuService->update(
            $menu,
            $request->validated()
        );

        $menu->append('image_url');

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil diperbarui.',
            'data' => $menu,
        ]);
    }

    public function destroy(Menu $menu)
    {
        $this->menuService->delete($menu);

        return response()->json([
            'success' => true,
            'message' => 'Menu berhasil dihapus.',
        ]);
    }
}