<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBusinessProfileRequest;
use App\Services\BusinessProfileService;
use Illuminate\Http\JsonResponse;

class BusinessProfileController extends Controller
{
    public function __construct(
        private BusinessProfileService $service
    ) {}

    /**
     * Profil publik untuk landing page.
     */
    public function publicProfile(): JsonResponse
    {
        $profile = $this->service->getPublicProfile();

        return response()->json([
            'success' => true,
            'message' => $profile
                ? 'Profil usaha berhasil diambil.'
                : 'Profil usaha belum tersedia.',
            'data' => $profile,
        ]);
    }

    /**
     * Profil untuk admin.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->getAdminProfile(),
        ]);
    }

    /**
     * Membuat atau memperbarui profil.
     */
    public function update(
        UpdateBusinessProfileRequest $request
    ): JsonResponse {
        $profile = $this->service->save(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Profil usaha berhasil disimpan.',
            'data' => $profile,
        ]);
    }
}