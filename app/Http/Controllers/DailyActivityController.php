<?php

namespace App\Http\Controllers;

use App\Models\DailyActivity;
use App\Models\Rfm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyActivityController extends Controller
{
    public function index(Request $request)
    {
        $activities = DailyActivity::with(['user', 'rfm'])
            ->whereNotIn('activity_type', ['login', 'logout'])
            ->when(
                auth()->user()->role === 'technician',
                fn ($q) => $q->where('user_id', auth()->id())
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('activity_type', 'like', "%{$search}%")
                        ->orWhereHas('rfm', fn ($q) => $q->where('rfm_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $rfms = Rfm::where('assigned_to', auth()->id())
            ->latest()
            ->get(['id', 'rfm_number', 'title']);

        return view('daily-activity.index', compact('activities', 'rfms'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateActivity($request);
        $validated['user_id'] = auth()->id();

        DailyActivity::create($validated);

        return redirect()->route('daily-activity.index')->with('success', 'Aktivitas harian berhasil disimpan.');
    }

    public function update(Request $request, DailyActivity $dailyActivity)
    {
        $this->ensureOwnActivity($dailyActivity);

        $dailyActivity->update($this->validateActivity($request));

        return redirect()->route('daily-activity.index')->with('success', 'Aktivitas harian berhasil diperbarui.');
    }

    public function destroy(DailyActivity $dailyActivity)
    {
        $this->ensureOwnActivity($dailyActivity);

        $dailyActivity->delete();

        return redirect()->route('daily-activity.index')->with('success', 'Aktivitas harian berhasil dihapus.');
    }

    private function validateActivity(Request $request): array
    {
        return $request->validate([
            'date' => ['required', 'date'],
            'activity_type' => ['required', 'string', 'max:100', 'not_in:login,logout'],
            'rfm_id' => [
                'nullable',
                Rule::exists('rfms', 'id')->where(fn ($q) => $q->where('assigned_to', auth()->id())),
            ],
            'description' => ['required', 'string', 'max:1000'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);
    }

    private function ensureOwnActivity(DailyActivity $dailyActivity): void
    {
        abort_if(in_array($dailyActivity->activity_type, ['login', 'logout'], true), 404);
        abort_unless($dailyActivity->user_id === auth()->id(), 403);
    }
}
