<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dgu;
use App\Services\Telemetry\TelemetryIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetryIngestController extends Controller
{
    public function store(Request $request, string $publicId, TelemetryIngestService $ingest): JsonResponse
    {
        $dgu = Dgu::query()->where('public_id', $publicId)->firstOrFail();

        $token = $request->bearerToken() ?? $request->header('X-Dgu-Token');
        if (! $dgu->verifiesTelemetryToken($token)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'recorded_at' => ['nullable', 'date'],
            'values' => ['required', 'array'],
        ]);

        $snapshot = $ingest->ingest($dgu, $validated['values'], $validated['recorded_at'] ?? null);

        return response()->json([
            'id' => $snapshot->id,
            'dgu_id' => $dgu->id,
            'public_id' => $dgu->public_id,
            'recorded_at' => $snapshot->recorded_at->toIso8601String(),
        ], 201);
    }
}
