<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Device::query()->withCount(['readings', 'alerts'])->latest('last_seen_at')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_uid' => ['required', 'string', 'max:255', 'unique:devices,device_uid'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'firmware_version' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Device::query()->create($data), 201);
    }

    public function show(Device $device): JsonResponse
    {
        return response()->json($device->load(['readings' => fn ($query) => $query->latest('measured_at')->limit(50), 'alerts']));
    }

    public function update(Request $request, Device $device): JsonResponse
    {
        $device->update($request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:online,offline,maintenance'],
            'firmware_version' => ['nullable', 'string', 'max:255'],
        ]));

        return response()->json($device);
    }

    public function destroy(Device $device): JsonResponse
    {
        $device->update(['status' => 'offline']);

        return response()->json(['message' => 'Device dinonaktifkan.']);
    }
}
