@extends('layouts.app')

@section('header')
    Dashboard
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-indigo-600">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Total RFM</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $totalRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Menunggu Approval</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $openRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-sky-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Assigned</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $assignedRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Dikerjakan</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $progressRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Menunggu Review</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $pendingReviewRfm }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Closed</h3>
        <p class="text-3xl font-bold text-gray-800">{{ $resolvedRfm }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tiket per Status</h3>
        <div class="relative h-64">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tiket per Kategori</h3>
        <div class="relative h-64">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Tiket per Priority</h3>
        <div class="relative h-64">
            <canvas id="priorityChart"></canvas>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Trend Tiket 30 Hari</h3>
        <div class="relative h-64">
            <canvas id="trendChart"></canvas>
        </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusData = @json($statusChart);
    const categoryData = @json($categoryChart);
    const priorityData = @json($priorityChart);
    const trendData = @json($trendChart);
    const gridColor = 'rgba(156, 163, 175, 0.2)';

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusData.labels,
            datasets: [{
                data: statusData.data,
                backgroundColor: statusData.colors,
                borderWidth: 1,
                borderColor: '#ffffff',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
            },
        },
    });

    function barChart(id, dataset) {
        new Chart(document.getElementById(id), {
            type: 'bar',
            data: {
                labels: dataset.labels,
                datasets: [{
                    label: 'Jumlah Tiket',
                    data: dataset.data,
                    backgroundColor: dataset.colors,
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: gridColor },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    barChart('categoryChart', categoryData);
    barChart('priorityChart', priorityData);

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trendData.labels,
            datasets: [{
                label: 'Tiket Masuk',
                data: trendData.data,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 2,
                pointBackgroundColor: '#6366f1',
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: gridColor },
                },
                x: {
                    grid: { display: false },
                    ticks: { maxTicksLimit: 10 },
                },
            },
        },
    });
});
</script>
@endsection
