<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorReading;
use App\Services\SensorIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReadingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $readings = SensorReading::query()
            ->with('device')
            ->when($request->string('device')->isNotEmpty(), fn ($query) => $query->whereHas('device', fn ($deviceQuery) => $deviceQuery->where('device_uid', $request->string('device'))))
            ->when($request->string('risk')->isNotEmpty(), fn ($query) => $query->where('risk_level', strtoupper((string) $request->string('risk'))))
            ->latest('measured_at')
            ->paginate(50);

        return response()->json($readings);
    }

    public function latest(): JsonResponse
    {
        return response()->json(SensorReading::query()->with('device')->latest('measured_at')->limit(10)->get());
    }

    public function byDevice(Device $device): JsonResponse
    {
        return response()->json($device->readings()->latest('measured_at')->paginate(50));
    }

    public function ingest(Request $request, SensorIngestionService $ingestionService): JsonResponse
    {
        $token = config('services.iot.ingest_token');

        if ($token && $request->bearerToken() !== $token) {
            return response()->json(['message' => 'Invalid ingest token.'], 401);
        }

        $payload = $request->validate([
            'event_id' => ['required', 'string', 'max:255'],
            'device_id' => ['required', 'string', 'max:255'],
            'measured_at' => ['required', 'date'],
            'ec_raw' => ['required', 'numeric'],
            'temperature' => ['required', 'numeric'],
            'ec_corrected' => ['nullable', 'numeric'],
            'ec_rate' => ['nullable', 'numeric'],
            'fuzzy_score' => ['required', 'numeric', 'between:0,100'],
            'risk_level' => ['required', 'in:NORMAL,WASPADA,BAHAYA,normal,waspada,bahaya'],
            'is_backfill' => ['sometimes', 'boolean'],
            'firmware_version' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $ingestionService->ingest($payload);

        return response()->json($result['ack'], $result['duplicate'] ? 200 : 201);
    }
}
