<?php

namespace App\Http\Controllers;

use App\Models\Rfm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RfmController extends Controller
{
    public const CATEGORIES = ['Hardware', 'Software', 'Jaringan', 'Akun', 'Lainnya'];

    public function index(Request $request)
    {
        $role = auth()->user()->role;

        $rfms = Rfm::with(['user', 'assignedTo'])
            // staff hanya lihat tiketnya sendiri, technician hanya tiket yang di-assign ke dia
            ->when($role === 'staff', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($role === 'technician', fn ($q) => $q->where('assigned_to', auth()->id()))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn ($q, $p) => $q->where('priority', $p))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->search, fn ($q, $k) => $q->where('title', 'like', "%{$k}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('rfm.index', [
            'rfms' => $rfms,
            'categories' => self::CATEGORIES,
            'technicians' => User::where('role', 'technician')->orderBy('name')->get(),
            'pendingReview' => Rfm::pendingReview()->where('user_id', auth()->id())->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureNoPendingReview();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:' . implode(',', self::CATEGORIES)],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'open';

        // Retry sekali kalau nomor bentrok karena dua request dibuat bersamaan.
        for ($attempt = 1; ; $attempt++) {
            try {
                $validated['rfm_number'] = Rfm::nextNumber();
                Rfm::create($validated);
                break;
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                if ($attempt >= 2) {
                    throw $e;
                }
            }
        }

        return redirect()->route('rfm.index')->with('success', 'Tiket RFM berhasil dibuat, menunggu approval HRD.');
    }

    public function update(Request $request, Rfm $rfm)
    {
        abort_unless($rfm->user_id === auth()->id() && $rfm->status === 'open', 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', 'in:' . implode(',', self::CATEGORIES)],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $rfm->update($validated);

        return redirect()->route('rfm.index')->with('success', 'Tiket RFM berhasil diperbarui.');
    }

    public function destroy(Rfm $rfm)
    {
        $ownOpenTicket = $rfm->user_id === auth()->id() && $rfm->status === 'open';

        abort_unless($ownOpenTicket || auth()->user()->role === 'admin', 403);

        $rfm->delete();

        return redirect()->route('rfm.index')->with('success', 'Tiket RFM berhasil dihapus.');
    }

    public function approve(Request $request, Rfm $rfm)
    {
        abort_unless($rfm->status === 'open', 403);

        $validated = $request->validate([
            'assigned_to' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'technician'),
            ],
        ]);

        $rfm->update([
            'status' => 'assigned',
            'assigned_to' => $validated['assigned_to'],
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('rfm.index')->with('success', 'Tiket disetujui dan di-assign ke teknisi.');
    }

    public function reject(Request $request, Rfm $rfm)
    {
        abort_unless($rfm->status === 'open', 403);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string'],
        ]);

        $rfm->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => auth()->id(),
        ]);

        return redirect()->route('rfm.index')->with('success', 'Tiket ditolak.');
    }

    public function start(Rfm $rfm)
    {
        abort_unless($rfm->status === 'assigned', 403);
        $this->ensureAssignedTechnician($rfm);

        $rfm->update(['status' => 'in_progress']);

        return redirect()->route('rfm.index')->with('success', 'Tiket mulai dikerjakan.');
    }

    public function resolve(Rfm $rfm)
    {
        abort_unless($rfm->status === 'in_progress', 403);
        $this->ensureAssignedTechnician($rfm);

        $rfm->update(['status' => 'resolved', 'resolved_at' => now()]);

        return redirect()->route('rfm.index')->with('success', 'Tiket selesai, menunggu review karyawan.');
    }

    /** Middleware hanya cek role; teknisi tetap hanya boleh mengerjakan tiket yang di-assign ke dia. */
    private function ensureAssignedTechnician(Rfm $rfm): void
    {
        abort_unless(
            $rfm->assigned_to === auth()->id() || auth()->user()->role === 'admin',
            403
        );
    }

    public function review(Request $request, Rfm $rfm)
    {
        abort_unless($rfm->user_id === auth()->id() && $rfm->status === 'resolved', 403);

        $validated = $request->validate([
            'review_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review_comment' => ['nullable', 'string'],
        ]);

        $rfm->update([
            'status' => 'closed',
            'review_rating' => $validated['review_rating'],
            'review_comment' => $validated['review_comment'] ?? null,
            'reviewed_at' => now(),
            'closed_at' => now(),
        ]);

        return redirect()->route('rfm.index')->with('success', 'Review terkirim, tiket ditutup.');
    }

    /** Review wajib: karyawan tidak bisa membuat tiket baru sebelum mereview tiket resolved miliknya. */
    private function ensureNoPendingReview(): void
    {
        $pending = Rfm::pendingReview()->where('user_id', auth()->id())->exists();

        if ($pending) {
            throw ValidationException::withMessages([
                'title' => 'Selesaikan review tiket yang sudah resolved terlebih dahulu.',
            ]);
        }
    }
}
