<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard System - Status Barang & PO')</title>

    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans text-gray-800 antialiased min-h-screen flex flex-col" x-data="{ sidebarOpen: false }">
    @php
        $currentPath = request()->path();
        if (str_starts_with($currentPath, 'supervisor')) {
            $sidebarBg = 'bg-[#1E93AB]';
            $activeRole = 'supervisor';
        } elseif (str_starts_with($currentPath, 'delivery')) {
            $sidebarBg = 'bg-[#79B440]';
            $activeRole = 'delivery';
        } else {
            $sidebarBg = 'bg-[#E62727]';
            $activeRole = 'admin';
        }
    @endphp

    <div class="flex-1 flex overflow-hidden">
        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-cloak
             x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden"
             @click="sidebarOpen = false">
        </div>

        <!-- Sidebar (Desktop & Mobile Drawer) -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 text-white transform transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 {{ $sidebarBg }} shadow-xl flex flex-col"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <!-- Sidebar Header / Logo -->
            <div class="p-5 border-b border-white/20 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center font-bold text-xl shadow-inner tracking-wider">
                        IPM
                    </div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight tracking-wide">STATUS BARANG</h1>
                        <p class="text-xs text-white/80 font-medium">CV. Indomulti Prakarsa Mandiri</p>
                    </div>
                </div>
                <!-- Close Button (Mobile) -->
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-md hover:bg-white/20 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <div class="text-[11px] font-semibold text-white/70 uppercase tracking-wider px-3 mb-2">
                    Menu Utama
                </div>

                <!-- ADMIN GUDANG -->
                <a href="{{ route('admin') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-semibold transition-all duration-200 {{ $activeRole === 'admin' ? 'bg-white text-[#E62727] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>ADMIN GUDANG</span>
                </a>

                <!-- SUPERVISOR GUDANG -->
                <a href="{{ route('supervisor') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-semibold transition-all duration-200 {{ $activeRole === 'supervisor' ? 'bg-white text-[#1E93AB] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <span>SUPERVISOR GUDANG</span>
                </a>

                <!-- DELIVERY -->
                <a href="{{ route('delivery') }}"
                   class="flex items-center space-x-3 px-4 py-3 rounded-lg font-semibold transition-all duration-200 {{ $activeRole === 'delivery' ? 'bg-white text-[#79B440] shadow-md' : 'text-white hover:bg-white/15' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <span>DELIVERY</span>
                </a>

                <div class="pt-4 mt-4 border-t border-white/20">
                    <div class="text-[11px] font-semibold text-white/70 uppercase tracking-wider px-3 mb-2">
                        Publik
                    </div>
                    <a href="{{ route('tracking') }}" target="_blank"
                       class="flex items-center space-x-3 px-4 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/15 transition-colors">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        <span>Halaman Tracking ↗</span>
                    </a>
                </div>
            </nav>

            <!-- Sidebar Footer Status -->
            <div class="p-4 border-t border-white/20 text-xs text-white/80">
                <div class="flex items-center justify-between">
                    <span>Server Status</span>
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/30 text-white font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Online
                    </span>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
                <div class="px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <!-- Mobile Hamburger Button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                                type="button"
                                class="lg:hidden p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h2 class="text-xl font-bold text-gray-900 tracking-tight">Dashboard System</h2>
                    </div>

                    <!-- User Profile & Logout -->
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-full bg-red-100 text-[#E62727] flex items-center justify-center font-bold text-sm shadow-inner">
                                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-sm">
                                <span class="text-gray-500">Welcome, </span>
                                <span class="font-semibold text-gray-800">{{ Auth::user()->name ?? 'Admin' }}</span>
                            </div>
                        </div>

                        <div class="h-5 w-px bg-gray-200 hidden sm:block"></div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-md text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6 text-center text-xs text-gray-500">
                Hak cipta © 2026 Dashboard System. All rights reserved. Version 1.0.0.
            </footer>
        </div>
    </div>

    <!-- Global Toast Component -->
    <div x-data="{
            toasts: [],
            add(msg, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, msg, type });
                setTimeout(() => this.remove(id), 4000);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
         }"
         @toast-notify.window="add($event.detail.message, $event.detail.type)"
         class="fixed bottom-5 right-5 z-50 flex flex-col space-y-2 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-cloak
                 x-show="true"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-2"
                 :class="{
                     'bg-emerald-600 text-white': toast.type === 'success',
                     'bg-red-600 text-white': toast.type === 'error',
                     'bg-blue-600 text-white': toast.type === 'info',
                     'bg-amber-600 text-white': toast.type === 'warning'
                 }"
                 class="pointer-events-auto p-4 rounded-xl shadow-lg flex items-center justify-between text-sm font-medium">
                <span x-text="toast.msg"></span>
                <button @click="remove(toast.id)" class="ml-3 text-white/80 hover:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @stack('scripts')
</body>
</html>
