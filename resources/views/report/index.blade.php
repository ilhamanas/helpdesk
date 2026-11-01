@extends('layouts.app')

@section('header')
    Report Helpdesk
@endsection

@section('content')
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan KPI</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-indigo-600">
            <h4 class="text-gray-600 text-sm font-semibold mb-2">Total RFM</h4>
            <p class="text-3xl font-bold text-gray-800">{{ $totalRfms }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
            <h4 class="text-gray-600 text-sm font-semibold mb-2">Selesai</h4>
            <p class="text-3xl font-bold text-gray-800">{{ $closedRfms }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
            <h4 class="text-gray-600 text-sm font-semibold mb-2">Ditolak</h4>
            <p class="text-3xl font-bold text-gray-800">{{ $rejectedRfms }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
            <h4 class="text-gray-600 text-sm font-semibold mb-2">Waktu Rata-rata (jam)</h4>
            <p class="text-3xl font-bold text-gray-800">{{ $avgResolutionHours }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-amber-500">
            <h4 class="text-gray-600 text-sm font-semibold mb-2">Rating Rata-rata</h4>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($avgRating ?? 0, 1) }}/5</p>
        </div>
    </div>
</div>

<div class="mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Laporan</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Kategori</option>
                    @foreach(['Hardware', 'Software', 'Jaringan', 'Akun', 'Lainnya'] as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="open" @selected(request('status') === 'open')>Menunggu Approval</option>
                    <option value="assigned" @selected(request('status') === 'assigned')>Assigned</option>
                    <option value="in_progress" @selected(request('status') === 'in_progress')>Dikerjakan</option>
                    <option value="resolved" @selected(request('status') === 'resolved')>Menunggu Review</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Priority</option>
                    <option value="low" @selected(request('priority') === 'low')>Low</option>
                    <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
                    <option value="high" @selected(request('priority') === 'high')>High</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Filter</button>
                @if(request()->hasAny(['date_from', 'date_to', 'category', 'status', 'priority']))
                    <a href="{{ route('report.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Laporan RFM</h3>
            <button onclick="window.print()" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium">Cetak</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr class="text-gray-700">
                        <th class="text-left py-4 px-4 font-semibold">No RFM</th>
                        <th class="text-left py-4 px-4 font-semibold">Judul</th>
                        <th class="text-left py-4 px-4 font-semibold">Kategori</th>
                        <th class="text-left py-4 px-4 font-semibold">Priority</th>
                        <th class="text-left py-4 px-4 font-semibold">Status</th>
                        <th class="text-left py-4 px-4 font-semibold">Requester</th>
                        <th class="text-left py-4 px-4 font-semibold">Rating</th>
                        <th class="text-left py-4 px-4 font-semibold">Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rfms as $rfm)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-4 px-4 font-mono text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded">{{ $rfm->rfm_number ?? '-' }}</td>
                        <td class="py-4 px-4">{{ $rfm->title }}</td>
                        <td class="py-4 px-4 text-gray-600">{{ $rfm->category }}</td>
                        <td class="py-4 px-4">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold 
                                @if($rfm->priority === 'low') bg-gray-100 text-gray-700
                                @elseif($rfm->priority === 'medium') bg-amber-100 text-amber-700
                                @else bg-red-100 text-red-700
                                @endif">
                                {{ ucfirst($rfm->priority) }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold 
                                @if($rfm->status === 'open') bg-yellow-100 text-yellow-700
                                @elseif($rfm->status === 'assigned') bg-blue-100 text-blue-700
                                @elseif($rfm->status === 'in_progress') bg-indigo-100 text-indigo-700
                                @elseif($rfm->status === 'resolved') bg-purple-100 text-purple-700
                                @elseif($rfm->status === 'rejected') bg-red-100 text-red-700
                                @else bg-green-100 text-green-700
                                @endif">
                                @switch($rfm->status)
                                    @case('open') Menunggu Approval @break
                                    @case('assigned') Assigned @break
                                    @case('in_progress') Dikerjakan @break
                                    @case('resolved') Menunggu Review @break
                                    @case('rejected') Ditolak @break
                                    @case('closed') Closed @break
                                @endswitch
                            </span>
                        </td>
                        <td class="py-4 px-4 text-gray-600">{{ $rfm->user->name ?? '-' }}</td>
                        <td class="py-4 px-4">
                            @if($rfm->review_rating)
                                <div class="text-xs text-amber-500">{{ str_repeat('★', $rfm->review_rating) }}{{ str_repeat('☆', 5 - $rfm->review_rating) }} <span class="text-gray-500">{{ $rfm->review_rating }}/5</span></div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-gray-600 text-xs">{{ $rfm->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 px-6 text-center text-gray-500">Belum ada data RFM.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $rfms->links() }}
        </div>
    </div>
</div>

<div class="mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Laporan Daily Activity</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Teknisi</label>
                <select name="technician_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Teknisi</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" @selected(request('technician_id') == $tech->id)>{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Filter</button>
        </form>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 p-3 rounded-lg mb-4 text-sm">
            <p>Total Durasi Kerja: <strong>{{ intdiv($totalDurationMinutes, 60) }} jam {{ $totalDurationMinutes % 60 }} menit</strong></p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr class="text-gray-700">
                        <th class="text-left py-4 px-4 font-semibold">Tanggal</th>
                        <th class="text-left py-4 px-4 font-semibold">Teknisi</th>
                        <th class="text-left py-4 px-4 font-semibold">Tiket</th>
                        <th class="text-left py-4 px-4 font-semibold">Jenis Aktivitas</th>
                        <th class="text-left py-4 px-4 font-semibold">Deskripsi</th>
                        <th class="text-left py-4 px-4 font-semibold">Durasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-4 px-4 text-gray-600 text-xs whitespace-nowrap">{{ $activity->date->format('d M Y') }}</td>
                        <td class="py-4 px-4 text-gray-700">{{ $activity->user->name ?? '-' }}</td>
                        <td class="py-4 px-4">
                            @if($activity->rfm)
                                <span class="font-mono text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded">{{ $activity->rfm->rfm_number ?? '-' }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                {{ $activity->activity_type }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-gray-600">{{ $activity->description }}</td>
                        <td class="py-4 px-4 text-gray-600 text-xs whitespace-nowrap">
                            @if($activity->duration_minutes)
                                {{ intdiv($activity->duration_minutes, 60) }} j {{ $activity->duration_minutes % 60 }} m
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 px-6 text-center text-gray-500">Belum ada aktivitas harian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $activities->links('pagination::tailwind', ['pageName' => 'activity_page']) }}
        </div>
    </div>
</div>

<style media="print">
    .no-print { display: none; }
</style>
@endsection
