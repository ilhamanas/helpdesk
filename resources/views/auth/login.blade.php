<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Monitoring Helpdesk</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0b0f19] text-gray-100 min-h-screen flex m-0 p-0 overflow-hidden">
    <!-- Left Panel (50%) -->
    <div class="w-1/2 bg-[#0b0f19] flex-col justify-between p-12 relative border-r border-gray-800/60 hidden lg:flex">
        <!-- Background office illustration with 60% opacity/blur overlay -->
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=1200&q=80'); filter: blur(3px);"></div>
        <div class="absolute inset-0 bg-[#0b0f19]/75 backdrop-blur-[2px]"></div>

        <div class="relative z-10 flex items-center space-x-3">
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-bold p-2 rounded-lg text-lg tracking-wider flex items-center justify-center w-9 h-9 shadow-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span class="font-bold text-white tracking-wider text-sm">Monitoring Helpdesk</span>
        </div>

        <div class="relative z-10 my-auto pr-8">
            <h1 class="text-4xl font-bold text-white mb-10 leading-tight">Monitoring Helpdesk.</h1>
            
            <div class="space-y-6">
                <div class="flex items-start space-x-4">
                    <div class="bg-gray-800/80 p-2.5 rounded-xl border border-gray-700/50 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm">Tracking tiket real-time</h3>
                        <p class="text-gray-400 text-xs mt-0.5">Pantau status tiket dari submit hingga resolved dalam satu dashboard.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="bg-gray-800/80 p-2.5 rounded-xl border border-gray-700/50 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm">Manajemen tim</h3>
                        <p class="text-gray-400 text-xs mt-0.5">Assign tiket ke technician dan monitor progress aktivitas harian.</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <div class="bg-gray-800/80 p-2.5 rounded-xl border border-gray-700/50 text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm">Laporan & analisis</h3>
                        <p class="text-gray-400 text-xs mt-0.5">Generate laporan performa dan insight tiket per periode.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 text-xs text-gray-400 font-sans">
            &copy; 2026 Monitoring Helpdesk
        </div>
    </div>

    <!-- Right Panel (50%) -->
    <div class="w-full lg:w-1/2 bg-white text-gray-900 flex items-center justify-center p-8 lg:p-16">
        <div class="w-full max-w-md">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Masuk ke akun Anda</h2>
                <p class="text-sm text-gray-500">Masukkan username dan password untuk melanjutkan ke dashboard.</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Masukkan username"
                            class="w-full pl-11 pr-4 py-3 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-sm text-gray-800 shadow-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-11 pr-11 py-3 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-sm text-gray-800 shadow-sm">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm py-0.5">
                    <label class="flex items-center text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 mr-2.5"> 
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <button type="submit" 
                    class="w-full bg-[#5233ff] hover:bg-[#4323e0] text-white font-medium py-3 rounded-xl transition duration-200 text-sm shadow-md shadow-indigo-100">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
