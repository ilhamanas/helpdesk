@extends('layouts.app')

@section('header')
    RFM (Request For Maintenance)
@endsection

@section('content')
@php
    $statusColors = [
        'open' => 'bg-yellow-100 text-yellow-700',
        'assigned' => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-indigo-100 text-indigo-700',
        'resolved' => 'bg-purple-100 text-purple-700',
        'rejected' => 'bg-red-100 text-red-700',
        'closed' => 'bg-green-100 text-green-700',
    ];
    $statusLabels = [
        'open' => 'Menunggu Approval',
        'assigned' => 'Assigned',
        'in_progress' => 'Dikerjakan',
        'resolved' => 'Menunggu Review',
        'rejected' => 'Ditolak',
        'closed' => 'Closed',
    ];
    $priorityColors = [
        'low' => 'bg-gray-100 text-gray-700',
        'medium' => 'bg-amber-100 text-amber-700',
        'high' => 'bg-red-100 text-red-700',
    ];
    $blocked = $pendingReview->isNotEmpty();
@endphp

@if($blocked)
    <div class="bg-purple-50 border border-purple-200 text-purple-800 p-4 rounded-lg mb-4 text-sm flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.577-1-2.464-1-3.041 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="font-semibold mb-1">Review tiket wajib diselesaikan</p>
            <p class="mb-2">Selesaikan review tiket berikut sebelum bisa membuat tiket baru:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($pendingReview as $pr)
                    <li>
                        #{{ $pr->id }} — {{ $pr->title }}
                        <button onclick='openReviewModal(@json($pr))' class="ml-1 text-purple-700 font-semibold underline">Isi Review</button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

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
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul..."
            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 w-48">
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            @foreach($statusLabels as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="priority" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Priority</option>
            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $value => $label)
                <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Kategori</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white rounded-lg text-sm font-medium">Filter</button>
    </form>

    <button onclick="openModal('createModal')" @disabled($blocked)
        class="px-4 py-2 rounded-lg text-sm font-medium text-white {{ $blocked ? 'bg-gray-300 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700' }}"
        @if($blocked) title="Selesaikan review tiket terlebih dahulu" @endif>
        + Buat Tiket
    </button>
</div>

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-200 bg-gray-50">
            <tr class="text-gray-700">
                <th class="text-left py-4 px-4 font-semibold">ID</th>
                <th class="text-left py-4 px-4 font-semibold">Judul</th>
                <th class="text-left py-4 px-4 font-semibold">Kategori</th>
                <th class="text-left py-4 px-4 font-semibold">Priority</th>
                <th class="text-left py-4 px-4 font-semibold">Status</th>
                <th class="text-left py-4 px-4 font-semibold">Requester</th>
                <th class="text-left py-4 px-4 font-semibold">Teknisi</th>
                <th class="text-left py-4 px-4 font-semibold">Dibuat</th>
                <th class="text-center py-4 px-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rfms as $rfm)
            <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                <td class="py-4 px-4 text-gray-700">{{ $rfm->id }}</td>
                <td class="py-4 px-4">
                    <p class="font-medium text-gray-900">{{ $rfm->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($rfm->description, 60) }}</p>
                    @if($rfm->status === 'rejected' && $rfm->rejection_reason)
                        <p class="text-xs text-red-600 mt-1">Alasan: {{ $rfm->rejection_reason }}</p>
                    @endif
                </td>
                <td class="py-4 px-4 text-gray-600">{{ $rfm->category }}</td>
                <td class="py-4 px-4">
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $priorityColors[$rfm->priority] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ ucfirst($rfm->priority) }}
                    </span>
                </td>
                <td class="py-4 px-4">
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$rfm->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ $statusLabels[$rfm->status] ?? $rfm->status }}
                    </span>
                    @if($rfm->review_rating)
                        <div class="text-xs text-amber-500 mt-1">{{ str_repeat('★', $rfm->review_rating) }}{{ str_repeat('☆', 5 - $rfm->review_rating) }}</div>
                    @endif
                </td>
                <td class="py-4 px-4 text-gray-600">{{ $rfm->user->name ?? '-' }}</td>
                <td class="py-4 px-4 text-gray-600">{{ $rfm->assignedTo->name ?? '-' }}</td>
                <td class="py-4 px-4 text-gray-600 text-xs">{{ $rfm->created_at->format('d M Y') }}</td>
                <td class="py-4 px-4">
                    <div class="flex items-center justify-center gap-1">
                        @if($rfm->status === 'open')
                            @if($rfm->user_id === auth()->id())
                                <button onclick='openEditModal(@json($rfm))' class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                            @endif
                            <button onclick='openApproveModal(@json($rfm))' class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:bg-green-50 rounded-lg transition" title="Approve & Assign">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                            <button onclick='openRejectModal(@json($rfm))' class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition" title="Reject">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        @endif

                        @if($rfm->status === 'assigned')
                            <form action="{{ route('rfm.start', $rfm) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Mulai Kerjakan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </form>
                        @endif

                        @if($rfm->status === 'in_progress')
                            <form action="{{ route('rfm.resolve', $rfm) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Tandai Selesai">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>
                            </form>
                        @endif

                        @if($rfm->isPendingReview() && $rfm->user_id === auth()->id())
                            <button onclick='openReviewModal(@json($rfm))' class="inline-flex items-center justify-center w-8 h-8 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Isi Review">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </button>
                        @endif

                        @if($rfm->user_id === auth()->id() && $rfm->status === 'open' || auth()->user()->role === 'admin')
                            <form action="{{ route('rfm.destroy', $rfm) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tiket ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="py-8 px-6 text-center text-gray-500">Belum ada tiket RFM</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $rfms->links() }}</div>

{{-- Modal Buat Tiket --}}
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Buat Tiket RFM</h3>
            <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('rfm.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                        <option value="low" @selected(old('priority') === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority') === 'high')>High</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Tiket --}}
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Edit Tiket RFM</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" id="edit_title" name="title" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea id="edit_description" name="description" rows="4" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select id="edit_category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select id="edit_priority" name="priority" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('editModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Perbarui</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Approve & Assign --}}
<div id="approveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Approve & Assign Teknisi</h3>
            <button onclick="closeModal('approveModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="approveForm" method="POST" class="p-4 space-y-4">
            @csrf
            <p class="text-sm text-gray-600" id="approveTicketTitle"></p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assign ke Teknisi</label>
                <select name="assigned_to" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                    <option value="">Pilih teknisi</option>
                    @forelse($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                    @empty
                        <option value="" disabled>Belum ada user dengan role IT / Teknisi</option>
                    @endforelse
                </select>
                @if($technicians->isEmpty())
                    <p class="text-xs text-red-600 mt-1">Tambahkan user ber-role "IT / Teknisi" di Master User terlebih dahulu.</p>
                @endif
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('approveModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" @disabled($technicians->isEmpty()) class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 text-white rounded-lg text-sm font-medium">Approve</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Reject --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Tolak Tiket</h3>
            <button onclick="closeModal('rejectModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="rejectForm" method="POST" class="p-4 space-y-4">
            @csrf
            <p class="text-sm text-gray-600" id="rejectTicketTitle"></p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                <textarea name="rejection_reason" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('rejectModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">Tolak</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Review --}}
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Review Tiket</h3>
            <button onclick="closeModal('reviewModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="reviewForm" method="POST" class="p-4 space-y-4">
            @csrf
            <p class="text-sm text-gray-600" id="reviewTicketTitle"></p>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-2">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="review_rating" value="{{ $i }}" required class="peer sr-only">
                            <svg class="w-7 h-7 text-gray-300 peer-checked:text-amber-400 hover:text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.922-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.196-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </label>
                    @endfor
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Komentar (opsional)</label>
                <textarea name="review_comment" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Bagaimana hasil pengerjaannya?"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('reviewModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Kirim Review</button>
            </div>
        </form>
    </div>
</div>

<script>
const rfmBase = "{{ url('rfm') }}";

function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function openEditModal(rfm) {
    document.getElementById('edit_title').value = rfm.title;
    document.getElementById('edit_description').value = rfm.description;
    document.getElementById('edit_category').value = rfm.category;
    document.getElementById('edit_priority').value = rfm.priority;
    document.getElementById('editForm').action = `${rfmBase}/${rfm.id}`;
    openModal('editModal');
}

function openApproveModal(rfm) {
    document.getElementById('approveTicketTitle').textContent = `#${rfm.id} — ${rfm.title}`;
    document.getElementById('approveForm').action = `${rfmBase}/${rfm.id}/approve`;
    openModal('approveModal');
}

function openRejectModal(rfm) {
    document.getElementById('rejectTicketTitle').textContent = `#${rfm.id} — ${rfm.title}`;
    document.getElementById('rejectForm').action = `${rfmBase}/${rfm.id}/reject`;
    openModal('rejectModal');
}

function openReviewModal(rfm) {
    document.getElementById('reviewTicketTitle').textContent = `#${rfm.id} — ${rfm.title}`;
    document.getElementById('reviewForm').action = `${rfmBase}/${rfm.id}/review`;
    openModal('reviewModal');
}

@if($errors->any())
    openModal('createModal');
@endif
</script>
@endsection
