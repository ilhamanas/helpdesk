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
        $activities = $this->recentActivities();

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

    private function recentActivities()
    {
        $user = auth()->user();
        $events = collect();

        $ticketScope = function () use ($user) {
            $query = Rfm::query();

            if ($user->role === 'staff') {
                $query->where('user_id', $user->id);
            } elseif ($user->role === 'technician') {
                $query->where('assigned_to', $user->id);
            }

            return $query;
        };

        $ticketScope()->with('user')->latest('created_at')->take(5)->get()
            ->each(fn ($rfm) => $events->push($this->ticketEvent(
                $rfm->created_at,
                $rfm->user?->name,
                'Membuat tiket',
                'bg-indigo-100 text-indigo-700',
                $rfm
            )));

        $ticketScope()->with('approvedBy')->whereNotNull('approved_at')
            ->latest('approved_at')->take(5)->get()
            ->each(fn ($rfm) => $events->push($this->ticketEvent(
                $rfm->approved_at,
                $rfm->approvedBy?->name,
                'Menyetujui tiket',
                'bg-green-100 text-green-700',
                $rfm
            )));

        $ticketScope()->with('approvedBy')->whereNotNull('rejected_at')
            ->latest('rejected_at')->take(5)->get()
            ->each(fn ($rfm) => $events->push($this->ticketEvent(
                $rfm->rejected_at,
                $rfm->approvedBy?->name,
                'Menolak tiket',
                'bg-red-100 text-red-700',
                $rfm
            )));

        $ticketScope()->with('assignedTo')->whereNotNull('resolved_at')
            ->latest('resolved_at')->take(5)->get()
            ->each(fn ($rfm) => $events->push($this->ticketEvent(
                $rfm->resolved_at,
                $rfm->assignedTo?->name,
                'Menyelesaikan tiket',
                'bg-sky-100 text-sky-700',
                $rfm
            )));

        $ticketScope()->with('user')->whereNotNull('reviewed_at')
            ->latest('reviewed_at')->take(5)->get()
            ->each(fn ($rfm) => $events->push($this->ticketEvent(
                $rfm->reviewed_at,
                $rfm->user?->name,
                'Mereview & menutup tiket',
                'bg-purple-100 text-purple-700',
                $rfm
            )));

        DailyActivity::with('user')->whereIn('activity_type', ['login', 'logout'])
            ->latest()->take(5)->get()
            ->each(function ($activity) use ($events) {
                $events->push([
                    'at' => $activity->created_at,
                    'user' => $activity->user?->name ?? '-',
                    'action' => $activity->activity_type === 'login'
                        ? 'Login ke sistem'
                        : 'Logout dari sistem',
                    'badge' => $activity->activity_type === 'login'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-gray-200 text-gray-700',
                    'ticket' => '-',
                ]);
            });

        return $events->sortByDesc('at')->take(5)->values();
    }

    private function ticketEvent($at, ?string $userName, string $action, string $badge, Rfm $rfm): array
    {
        return [
            'at' => $at,
            'user' => $userName ?? '-',
            'action' => $action,
            'badge' => $badge,
            'ticket' => trim(($rfm->rfm_number ?? '-') . ' · ' . $rfm->title),
        ];
    }
}
