@extends('layouts.app')

@section('header')
    Dashboard
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-600">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Total RFM</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $totalRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Open</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $openRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">In Progress</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $progressRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Resolved</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $resolvedRfm }}</p>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Activities</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200">
                <tr class="text-gray-600">
                    <th class="text-left py-3 px-4">User</th>
                    <th class="text-left py-3 px-4">Activity</th>
                    <th class="text-left py-3 px-4">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">{{ $activity->user->name }}</td>
                    <td class="py-3 px-4">{{ $activity->activity_type }}</td>
                    <td class="py-3 px-4">{{ $activity->date->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-4 px-4 text-center text-gray-500">No activities yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
