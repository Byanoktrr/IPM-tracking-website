<?php

namespace App\Http\Controllers;

use App\Models\Ekspedisi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EkspedisiController extends Controller
{
    /**
     * Display a listing of the expeditions.
     */
    public function index(): JsonResponse
    {
        $ekspedisi = Ekspedisi::orderBy('nama_ekspedisi', 'asc')->get();

        return response()->json($ekspedisi);
    }

    /**
     * Store a newly created expedition.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_ekspedisi' => ['required', 'string', 'max:255'],
            'link_ekspedisi' => ['nullable', 'string', 'max:1000'],
        ]);

        $ekspedisi = Ekspedisi::create($validated);

        return response()->json([
            'message' => 'Ekspedisi berhasil ditambahkan',
            'data' => $ekspedisi,
        ], 201);
    }

    /**
     * Remove the specified expedition.
     */
    public function destroy(int $id): JsonResponse
    {
        $ekspedisi = Ekspedisi::findOrFail($id);
        $ekspedisi->delete();

        return response()->json([
            'message' => 'Ekspedisi berhasil dihapus',
        ]);
    }
}
