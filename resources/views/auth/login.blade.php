<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Dashboard System Status Barang & PO</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen text-gray-800 antialiased font-sans flex flex-col justify-between"
      x-data="{ showPassword: false, email: '{{ old('email', 'admin@gmail.com') }}', password: '{{ old('email') ? '' : 'admin123' }}' }">
    
    <!-- Top Bar -->
    <header class="bg-white border-b border-gray-200 py-3.5 px-4 sm:px-8 shadow-xs">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <img src="/images/IPM-logo.jpg" alt="IPM Logo" class="h-9 w-auto object-contain rounded-md shadow-2xs">
                <div>
                    <h1 class="text-sm sm:text-base font-extrabold text-gray-900 leading-tight">STATUS BARANG</h1>
                    <p class="text-[11px] text-gray-500 font-medium">CV. Indomulti Prakarsa Mandiri</p>
                </div>
            </div>

            <a href="{{ route('tracking') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition">
                <span>Lacak Pesanan Publik ↗</span>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 my-6">
        <div class="w-full max-w-md">
            <!-- Login Card -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
                <!-- Card Header Banner -->
                <div class="bg-gradient-to-r from-[#E62727] via-[#d01f1f] to-[#b31414] p-6 sm:p-8 text-white text-center relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-white shadow-inner font-bold text-2xl">
                            IPM
                        </div>
                        <h2 class="text-2xl font-black tracking-tight">Masuk ke Sistem</h2>
                        <p class="text-xs sm:text-sm text-red-100 mt-1 font-medium">
                            Status Barang & Purchase Order Management
                        </p>
                    </div>
                    <!-- Background Decoration -->
                    <div class="absolute -right-8 -bottom-8 w-32 h-32 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
                    <div class="absolute -left-8 -top-8 w-32 h-32 rounded-full bg-black/10 blur-xl pointer-events-none"></div>
                </div>

                <!-- Form Body -->
                <div class="p-6 sm:p-8 space-y-6">
                    <!-- Session Status or Errors -->
                    @if(session('success'))
                        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold space-y-1">
                            @foreach($errors->all() as $error)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <span>{{ $error }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Default Seed Credential Hint Pill -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-xs text-gray-600 flex items-start justify-between gap-2">
                        <div>
                            <span class="font-bold text-gray-800 block text-[11px] uppercase tracking-wider mb-0.5">Akun Demo Default:</span>
                            <div class="font-mono text-[11px] text-gray-700">
                                <div>Email: <span class="font-bold text-[#E62727]">admin@gmail.com</span></div>
                                <div>Password: <span class="font-bold text-[#E62727]">admin123</span></div>
                            </div>
                        </div>
                        <button type="button" 
                                @click="email = 'admin@gmail.com'; password = 'admin123'" 
                                class="text-[10px] font-bold px-2 py-1 bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-100 text-gray-700 shrink-0 transition">
                            Isi Form
                        </button>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                        @csrf

                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Alamat Email
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </span>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       x-model="email"
                                       required 
                                       autofocus
                                       placeholder="admin@gmail.com"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 text-sm font-medium outline-hidden transition">
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Kata Sandi
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <input :type="showPassword ? 'text' : 'password'" 
                                       id="password" 
                                       name="password" 
                                       x-model="password"
                                       required 
                                       placeholder="••••••••"
                                       class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 text-sm font-medium outline-hidden transition">
                                <!-- Toggle Show / Hide Password -->
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-hidden">
                                    <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-between text-xs pt-1">
                            <label class="flex items-center space-x-2 cursor-pointer select-none">
                                <input type="checkbox" 
                                       name="remember" 
                                       checked
                                       class="w-4 h-4 text-[#E62727] rounded-sm focus:ring-[#E62727] border-gray-300">
                                <span class="text-gray-700 font-medium">Ingat Saya</span>
                            </label>
                            <span class="text-gray-400">Versi 1.0.0</span>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-[#E62727] to-[#b31414] hover:from-[#d01f1f] hover:to-[#9f1010] text-white font-bold text-sm shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 mt-2">
                            <span>Masuk ke Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </form>

                    <!-- Public Tracking Callout -->
                    <div class="pt-4 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500">
                            Ingin melacak paket atau pesanan pelanggan?
                        </p>
                        <a href="{{ route('tracking') }}" 
                           class="inline-block mt-1 text-xs font-bold text-[#E62727] hover:underline">
                            Buka Halaman Tracking Publik ↗
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 px-6 text-center text-xs text-gray-500">
        Hak cipta © 2026 Dashboard System. All rights reserved. CV. Indomulti Prakarsa Mandiri.
    </footer>
</body>
</html>
