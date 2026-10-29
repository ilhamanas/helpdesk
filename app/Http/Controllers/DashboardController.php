<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rfm;
use App\Models\DailyActivity;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRfm = Rfm::count();
        $openRfm = Rfm::where('status', 'open')->count();
        $progressRfm = Rfm::where('status', 'in_progress')->count();
        $resolvedRfm = Rfm::where('status', 'resolved')->count();
        $activities = DailyActivity::latest()->take(5)->get();

        return view('dashboard', compact('totalRfm', 'openRfm', 'progressRfm', 'resolvedRfm', 'activities'));
    }
}
