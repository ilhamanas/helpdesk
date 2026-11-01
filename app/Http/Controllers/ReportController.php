<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Models\Rfm;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $category = $request->category;
        $status = $request->status;
        $priority = $request->priority;

        $rfmQuery = Rfm::with(['user', 'assignedTo', 'approvedBy'])
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($category, fn ($q, $c) => $q->where('category', $c))
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->when($priority, fn ($q, $p) => $q->where('priority', $p));

        $totalRfms = (clone $rfmQuery)->count();
        $closedRfms = (clone $rfmQuery)->where('status', 'closed')->count();
        $rejectedRfms = (clone $rfmQuery)->where('status', 'rejected')->count();
        $openRfms = (clone $rfmQuery)->whereIn('status', ['open', 'assigned', 'in_progress', 'resolved'])->count();

        $avgRating = (clone $rfmQuery)->whereNotNull('review_rating')->avg('review_rating');

        $resolvedTickets = (clone $rfmQuery)->whereNotNull('resolved_at')->get();
        $totalHours = $resolvedTickets->sum(fn ($t) => $t->created_at->diffInHours($t->resolved_at));
        $avgResolutionHours = $resolvedTickets->count() > 0 ? round($totalHours / $resolvedTickets->count(), 1) : 0;

        $rfms = (clone $rfmQuery)->latest()->paginate(15)->withQueryString();

        $activityQuery = DailyActivity::with(['user', 'rfm'])
            ->whereNotIn('activity_type', ['login', 'logout'])
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->when($request->technician_id, fn ($q, $id) => $q->where('user_id', $id));

        $activities = (clone $activityQuery)->latest('date')->paginate(15, ['*'], 'activity_page')->withQueryString();
        $totalDurationMinutes = (clone $activityQuery)->sum('duration_minutes');

        $technicians = \App\Models\User::where('role', 'technician')->orderBy('name')->get();

        return view('report.index', compact(
            'totalRfms',
            'closedRfms',
            'rejectedRfms',
            'openRfms',
            'avgRating',
            'avgResolutionHours',
            'rfms',
            'activities',
            'totalDurationMinutes',
            'technicians'
        ));
    }
}
