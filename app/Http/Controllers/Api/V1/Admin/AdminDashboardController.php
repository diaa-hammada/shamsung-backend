<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalOrders     = MaintenanceRequest::count();
        $completedOrders = MaintenanceRequest::where('status', 'completed')->count();
        $pendingOrders   = MaintenanceRequest::whereNotIn('status', ['completed', 'cancelled'])->count();

        $maintenanceRevenue = (float) MaintenanceRequest::where('status', 'completed')->sum('estimated_cost');
        $accessoryRevenue   = (float) Order::sum('total_amount');
        $totalRevenue       = $maintenanceRevenue + $accessoryRevenue;

        $thisWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $lastWeekStart = $thisWeekStart->copy()->subWeek();
        $lastWeekEnd   = $thisWeekStart->copy()->subSecond();

        $thisWeekRevenue = $this->weekRevenue($thisWeekStart, Carbon::now());
        $lastWeekRevenue = $this->weekRevenue($lastWeekStart, $lastWeekEnd);

        $revenueChangePercent = $lastWeekRevenue > 0
            ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 1)
            : 0.0;

        return response()->json([
            'message' => 'Dashboard stats retrieved successfully',
            'data'    => [
                'total_orders'           => $totalOrders,
                'pending_orders'         => $pendingOrders,
                'completed_orders'       => $completedOrders,
                'revenue'                => $totalRevenue,
                'revenue_change_percent' => $revenueChangePercent,
            ],
        ], 200);
    }

    private function weekRevenue(Carbon $from, Carbon $to): float
    {
        $maintenance = (float) MaintenanceRequest::where('status', 'completed')
            ->whereBetween('updated_at', [$from, $to])
            ->sum('estimated_cost');

        $accessories = (float) Order::whereBetween('created_at', [$from, $to])
            ->sum('total_amount');

        return $maintenance + $accessories;
    }
}
