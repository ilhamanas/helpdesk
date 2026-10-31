@extends('layouts.app')

@section('header')
    Daily Activity
@endsection

@section('content')
@php
    $isTechnician = auth()->user()->role === 'technician';
@endphp

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-4 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="flex flex-wrap justify-between items-center gap-3 mb-4">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas / No RFM..."
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 w-56">
        <input type="date" name="date_from" value="{{ request('date_from') }}"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
        <span class="text-gray-500 text-sm">s.d.</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500">
        <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium">Filter</button>
        @if(request()->hasAny(['search', 'date_from', 'date_to']))
            <a href="{{ route('daily-activity.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Reset</a>
        @endif
    </form>

    @if($isTechnician)
    <button onclick="openModal('createModal')" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
        + Tambah Aktivitas
    </button>
    @endif
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-200 bg-gray-50">
            <tr class="text-gray-700">
                <th class="text-left py-4 px-4 font-semibold">No</th>
                <th class="text-left py-4 px-4 font-semibold">Tanggal</th>
                <th class="text-left py-4 px-4 font-semibold">Teknisi</th>
                <th class="text-left py-4 px-4 font-semibold">Tiket</th>
                <th class="text-left py-4 px-4 font-semibold">Jenis Aktivitas</th>
                <th class="text-left py-4 px-4 font-semibold">Deskripsi</th>
                <th class="text-left py-4 px-4 font-semibold">Durasi</th>
                @if($isTechnician)
                <th class="text-center py-4 px-4 font-semibold">Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($activities as $activity)
            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                <td class="py-4 px-4 text-gray-500">{{ $activities->firstItem() + $loop->index }}</td>
                <td class="py-4 px-4 text-gray-600 text-xs whitespace-nowrap">{{ $activity->date->format('d M Y') }}</td>
                <td class="py-4 px-4 text-gray-700">{{ $activity->user->name ?? '-' }}</td>
                <td class="py-4 px-4">
                    @if($activity->rfm)
                        <p class="font-mono text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded inline-block">{{ $activity->rfm->rfm_number ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($activity->rfm->title, 40) }}</p>
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
                        {{ trim((intdiv($activity->duration_minutes, 60) ? intdiv($activity->duration_minutes, 60) . ' j ' : '') . ($activity->duration_minutes % 60 ? $activity->duration_minutes % 60 . ' m' : '')) }}
                    @else
                        -
                    @endif
                </td>
                @if($isTechnician)
                <td class="py-4 px-4">
                    <div class="flex items-center justify-center gap-1">
                        <button onclick='openEditModal(@json($activity))' class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form action="{{ route('daily-activity.destroy', $activity) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus aktivitas ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $isTechnician ? 8 : 7 }}" class="py-8 px-6 text-center text-gray-500">Belum ada aktivitas harian.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $activities->links() }}
</div>

@if($isTechnician)
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Tambah Aktivitas Harian</h3>
            <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('daily-activity.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
                    <input type="number" name="duration_minutes" value="{{ old('duration_minutes') }}" min="1" max="1440" placeholder="Opsional" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Aktivitas</label>
                <input type="text" name="activity_type" value="{{ old('activity_type') }}" required maxlength="100" placeholder="Contoh: Perbaikan printer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiket RFM (opsional)</label>
                <select name="rfm_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                    <option value="">Tidak terkait tiket</option>
                    @foreach($rfms as $rfm)
                        <option value="{{ $rfm->id }}" @selected(old('rfm_id') == $rfm->id)>{{ $rfm->rfm_number }} · {{ $rfm->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3" required maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Edit Aktivitas Harian</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" id="edit_date" name="date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (menit)</label>
                    <input type="number" id="edit_duration" name="duration_minutes" min="1" max="1440" placeholder="Opsional" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Aktivitas</label>
                <input type="text" id="edit_activity_type" name="activity_type" required maxlength="100" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tiket RFM (opsional)</label>
                <select id="edit_rfm_id" name="rfm_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                    <option value="">Tidak terkait tiket</option>
                    @foreach($rfms as $rfm)
                        <option value="{{ $rfm->id }}">{{ $rfm->rfm_number }} · {{ $rfm->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="edit_description" name="description" rows="3" required maxlength="1000" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openEditModal(activity) {
    document.getElementById('edit_date').value = activity.date.substring(0, 10);
    document.getElementById('edit_duration').value = activity.duration_minutes ?? '';
    document.getElementById('edit_activity_type').value = activity.activity_type;
    document.getElementById('edit_rfm_id').value = activity.rfm_id ?? '';
    document.getElementById('edit_description').value = activity.description;
    document.getElementById('editForm').action = `{{ url('daily-activity') }}/${activity.id}`;
    openModal('editModal');
}

@if($errors->any())
    openModal('createModal');
@endif
</script>
@endif
@endsection
