<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Helpdesk')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen" x-data="{ sidebarOpen: false }">
        <aside class="w-64 bg-gray-900 text-white flex flex-col fixed inset-y-0 left-0 z-50 transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:z-auto" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-xl font-bold">Helpdesk</h1>
            </div>
            <nav class="flex-1 p-4 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('rfm.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('rfm.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>RFM</span>
                </a>
                @if(in_array(auth()->user()->role, ['technician', 'hrd', 'admin']))
                <a href="{{ route('daily-activity.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('daily-activity.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Daily Activity</span>
                </a>
                @endif
                @if(in_array(auth()->user()->role, ['hrd', 'admin']))
                <a href="{{ route('report.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('report.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Report</span>
                </a>
                @endif
                @if(auth()->user()->role === 'admin')
                <hr class="my-3 border-gray-700">
                <span class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengaturan</span>
                <a href="{{ route('settings.users.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('settings.users.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Master User</span>
                </a>
                <a href="{{ route('settings.roles.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('settings.roles.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Hak Akses</span>
                </a>
                @endif
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="mb-2 px-4 text-sm text-gray-400">{{ auth()->user()->name }}</div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 rounded-lg hover:bg-gray-800 text-red-400">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex items-center justify-between lg:justify-start">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h2 class="text-2xl font-semibold text-gray-800 flex-1 lg:flex-none">@yield('header')</h2>
            </header>

            <main class="flex-1 overflow-y-auto">
                <div class="p-4 sm:p-6">
                    @yield('content')
                </div>
            </main>
        </div>

        <div class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" @click="sidebarOpen = false" :class="sidebarOpen ? 'block' : 'hidden'"></div>
    </div>
</body>
</html>
