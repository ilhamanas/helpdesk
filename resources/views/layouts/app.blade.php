<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Helpdesk')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6 border-b border-gray-700">
                <h1 class="text-xl font-bold">Helpdesk</h1>
            </div>
            <nav class="flex-1 p-4">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('rfm.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('rfm.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <span>RFM</span>
                </a>
                <a href="{{ route('daily-activity.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('daily-activity.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <span>Daily Activity</span>
                </a>
                <a href="#" class="flex items-center px-4 py-3 mb-2 rounded-lg hover:bg-gray-800">
                    <span>Report</span>
                </a>
                <hr class="my-3 border-gray-700">
                <span class="flex items-center px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pengaturan</span>
                <a href="{{ route('settings.users.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('settings.users.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <span>Master User</span>
                </a>
                <a href="{{ route('settings.roles.index') }}" class="flex items-center px-4 py-3 mb-2 rounded-lg {{ request()->routeIs('settings.roles.*') ? 'bg-indigo-600' : 'hover:bg-gray-800' }}">
                    <span>Hak Akses</span>
                </a>
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

        <main class="flex-1 overflow-y-auto">
            <header class="bg-white shadow-sm p-6">
                <h2 class="text-2xl font-semibold text-gray-800">@yield('header')</h2>
            </header>
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
