<?php

namespace App\Http\Controllers;

use App\Models\Rfm;
use App\Models\DailyActivity;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRfm = Rfm::count();
        $openRfm = Rfm::where('status', 'open')->count();
        $assignedRfm = Rfm::where('status', 'assigned')->count();
        $progressRfm = Rfm::where('status', 'in_progress')->count();
        $pendingReviewRfm = Rfm::pendingReview()->count();
        $resolvedRfm = Rfm::where('status', 'closed')->count();
        $activities = DailyActivity::latest()->take(5)->get();

        $statusCounts = Rfm::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusChart = [
            'labels' => ['Open', 'Assigned', 'In Progress', 'Resolved', 'Closed', 'Rejected'],
            'data' => [
                (int) ($statusCounts['open'] ?? 0),
                (int) ($statusCounts['assigned'] ?? 0),
                (int) ($statusCounts['in_progress'] ?? 0),
                (int) ($statusCounts['resolved'] ?? 0),
                (int) ($statusCounts['closed'] ?? 0),
                (int) ($statusCounts['rejected'] ?? 0),
            ],
            'colors' => ['#6366f1', '#eab308', '#0ea5e9', '#8b5cf6', '#22c55e', '#ef4444'],
        ];

        $categoryCounts = Rfm::selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $categoryChart = [
            'labels' => RfmController::CATEGORIES,
            'data' => array_map(
                fn ($category) => (int) ($categoryCounts[$category] ?? 0),
                RfmController::CATEGORIES
            ),
            'colors' => ['#6366f1', '#0ea5e9', '#22c55e', '#f59e0b', '#8b5cf6'],
        ];

        $priorityCounts = Rfm::selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $priorityChart = [
            'labels' => ['Low', 'Medium', 'High'],
            'data' => [
                (int) ($priorityCounts['low'] ?? 0),
                (int) ($priorityCounts['medium'] ?? 0),
                (int) ($priorityCounts['high'] ?? 0),
            ],
            'colors' => ['#22c55e', '#f59e0b', '#ef4444'],
        ];

        $start = now()->subDays(29)->startOfDay();
        $dailyCounts = Rfm::where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $trendLabels = [];
        $trendData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trendLabels[] = $date->format('d M');
            $trendData[] = (int) ($dailyCounts[$date->toDateString()] ?? 0);
        }

        $trendChart = [
            'labels' => $trendLabels,
            'data' => $trendData,
        ];

        return view('dashboard', compact(
            'totalRfm',
            'openRfm',
            'assignedRfm',
            'progressRfm',
            'pendingReviewRfm',
            'resolvedRfm',
            'activities',
            'statusChart',
            'categoryChart',
            'priorityChart',
            'trendChart'
        ));
    }
}
