@extends('layouts.app')

@section('header')
    Master User
@endsection

@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-semibold text-gray-800">Daftar Users</h3>
    <button onclick="openModal('createModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
        + Tambah User
    </button>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg mb-4 text-sm">
        {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-200 bg-gray-50">
            <tr class="text-gray-700">
                <th class="text-left py-3 px-4 font-semibold">ID</th>
                <th class="text-left py-3 px-4 font-semibold">Nama</th>
                <th class="text-left py-3 px-4 font-semibold">Email</th>
                <th class="text-left py-3 px-4 font-semibold">Role</th>
                <th class="text-left py-3 px-4 font-semibold">Dibuat</th>
                <th class="text-center py-3 px-4 font-semibold">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr class="border-b border-gray-100 hover:bg-gray-50">
                <td class="py-3 px-4">{{ $user->id }}</td>
                <td class="py-3 px-4 font-medium text-gray-900">{{ $user->name }}</td>
                <td class="py-3 px-4 text-gray-600">{{ $user->email }}</td>
                <td class="py-3 px-4">
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold 
                        @if($user->role === 'admin') bg-red-100 text-red-700
                        @elseif($user->role === 'technician') bg-blue-100 text-blue-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="py-3 px-4 text-gray-600 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                <td class="py-3 px-4 text-center space-x-2">
                    <button onclick='openEditModal(@json($user))' class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">Edit</button>
                    <form action="{{ route('settings.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-6 px-4 text-center text-gray-500">Belum ada user</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Create Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Tambah User Baru</h3>
            <button onclick="closeModal('createModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="{{ route('settings.users.store') }}" method="POST" class="p-4 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->code }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeModal('createModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Edit User</h3>
            <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" class="p-4 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="edit_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select id="edit_role" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm bg-white">
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->code }}">{{ $role->name }}</option>
                    @endforeach
                </select>
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

function openEditModal(user) {
    document.getElementById('edit_name').value = user.name;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_role').value = user.role;
    document.getElementById('editForm').action = `{{ url('settings/users') }}/${user.id}`;
    openModal('editModal');
}
</script>
@endsection
