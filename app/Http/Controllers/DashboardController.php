<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }

    public function summary(): JsonResponse
    {
        $latest = SensorReading::query()->with('device')->latest('measured_at')->first();

        return response()->json([
            'stats' => [
                'total_devices' => Device::query()->count(),
                'online_devices' => Device::query()->where('status', 'online')->count(),
                'offline_devices' => Device::query()->where('status', 'offline')->count(),
                'active_alerts' => Alert::query()->whereIn('status', ['NEW', 'ACKNOWLEDGED'])->count(),
                'pending_sync' => SyncLog::query()->where('duplicate_detected', false)->where('received_at', '>=', now()->subHour())->count(),
            ],
            'latest' => $latest,
            'trend' => SensorReading::query()
                ->select(['measured_at', 'ec_corrected', 'temperature', 'fuzzy_score', 'risk_level'])
                ->latest('measured_at')
                ->limit(20)
                ->get()
                ->reverse()
                ->values(),
            'risk_distribution' => SensorReading::query()
                ->select('risk_level', DB::raw('count(*) as total'))
                ->groupBy('risk_level')
                ->pluck('total', 'risk_level'),
            'alerts' => Alert::query()->with(['device', 'reading'])->latest('occurred_at')->limit(5)->get(),
            'devices' => Device::query()->withCount(['readings', 'alerts'])->latest('last_seen_at')->get(),
        ]);
    }
}
