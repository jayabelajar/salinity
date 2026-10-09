<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(Alert::query()
            ->with(['device', 'reading'])
            ->when($request->string('status')->isNotEmpty(), fn ($query) => $query->where('status', strtoupper((string) $request->string('status'))))
            ->latest('occurred_at')
            ->paginate(30));
    }

    public function show(Alert $alert): JsonResponse
    {
        return response()->json($alert->load(['device', 'reading']));
    }

    public function acknowledge(Alert $alert): JsonResponse
    {
        $alert->update([
            'status' => 'ACKNOWLEDGED',
            'acknowledged_at' => now(),
        ]);

        return response()->json($alert);
    }

    public function resolve(Request $request, Alert $alert): JsonResponse
    {
        $alert->update([
            'status' => 'RESOLVED',
            'resolved_at' => now(),
            'notes' => $request->input('notes', $alert->notes),
        ]);

        return response()->json($alert);
    }

    public function notify(Alert $alert, NotificationService $notificationService): JsonResponse
    {
        $notificationService->notifyAlert($alert);

        return response()->json(['message' => 'Notification queued/sent according to configuration.']);
    }
}
