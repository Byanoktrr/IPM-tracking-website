<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tracking Purchase Order - CV. Indomulti Prakarsa Mandiri</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen text-gray-800 antialiased font-sans flex flex-col justify-between">
    <!-- Public Header -->
    <header class="bg-white border-b border-gray-200 shadow-xs sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3 sm:space-x-4">
                <img src="/images/IPM-logo.jpg" 
                     alt="IPM Logo" 
                     class="h-10 sm:h-12 w-auto object-contain rounded-md shadow-2xs">
                <div>
                    <h1 class="text-base sm:text-lg font-extrabold text-gray-900 leading-tight">
                        Tracking Purchase Order
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        CV. Indomulti Prakarsa Mandiri
                    </p>
                </div>
            </div>

            <a href="{{ route('admin') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                <span>Dashboard Internal</span>
            </a>
        </div>
    </header>

    <!-- Main Tracking Content -->
    <main class="flex-1 max-w-4xl w-full mx-auto px-4 sm:px-6 py-8 sm:py-12"
          x-data="trackingApp({{ Js::from($initialPoQuery) }}, {{ Js::from($initialPoData) }})">
        
        <!-- Search Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 mb-8">
            <div class="text-center max-w-xl mx-auto mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">
                    Cek Status & Lacak Pengiriman
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Masukkan nomor Purchase Order (PO) Anda untuk melihat progres pemrosesan dan status resi pengiriman.
                </p>
            </div>

            <form @submit.prevent="searchTracking()" class="flex flex-col sm:flex-row gap-3 max-w-xl mx-auto">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <input type="text" 
                           x-model="poNumber" 
                           required 
                           placeholder="Masukkan Nomor PO (cth: PO-2026-001)"
                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:border-red-600 focus:ring-2 focus:ring-red-600/20 text-sm font-medium outline-hidden transition">
                </div>
                <button type="submit" 
                        :disabled="loading"
                        class="px-6 py-3 rounded-xl bg-[#E62727] hover:bg-[#d01f1f] text-white font-bold text-sm shadow-md transition-all duration-200 disabled:opacity-50 flex items-center justify-center gap-2">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Lacak Pesanan</span>
                </button>
            </form>

            <!-- Alert Not Found -->
            <div x-cloak x-show="errorMessage" class="mt-4 max-w-xl mx-auto p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span x-text="errorMessage"></span>
            </div>
        </div>

        <!-- Tracking Result Detail -->
        <template x-if="poData">
            <div class="space-y-6">
                <!-- Large Status Header Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-400">Nomor Purchase Order</div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mt-0.5" x-text="poData.po"></h2>
                        <p class="text-sm text-gray-600 mt-1">Customer: <strong class="text-gray-800" x-text="poData.kostumer"></strong></p>
                    </div>

                    <!-- Large Status Badge -->
                    <div class="flex flex-col items-start md:items-end">
                        <span class="text-xs font-semibold text-gray-400 mb-1">Status Terkini:</span>
                        <div class="px-5 py-2 rounded-2xl text-base sm:text-lg font-extrabold border shadow-xs"
                             :class="getStatusBadgeClass(poData.status)"
                             x-text="poData.status || '-'">
                        </div>
                    </div>
                </div>

                <!-- Info Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kartu Informasi Pesanan -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="flex items-center space-x-2 text-gray-800 font-bold border-b border-gray-100 pb-3">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Informasi Pesanan</span>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between py-1.5 border-b border-gray-50">
                                <span class="text-gray-500">Tanggal PO:</span>
                                <span class="font-semibold text-gray-800" x-text="formatDate(poData.tgl_po)"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-gray-50">
                                <span class="text-gray-500">Tanggal Terima PO:</span>
                                <span class="font-semibold text-gray-800" x-text="formatDate(poData.tgl_terima_po)"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-gray-50">
                                <span class="text-gray-500">Nomor DO:</span>
                                <span class="font-semibold text-gray-800 font-mono" x-text="poData.do || '-'"></span>
                            </div>
                            <div class="flex justify-between py-1.5 border-b border-gray-50">
                                <span class="text-gray-500">Tanggal Terbit DO:</span>
                                <span class="font-semibold text-gray-800" x-text="formatDate(poData.tgl_terbit_do)"></span>
                            </div>
                            <div class="flex justify-between py-1.5">
                                <span class="text-gray-500">Estimasi Jeda Hari:</span>
                                <span class="font-semibold text-gray-800" x-text="poData.jedahari !== null ? poData.jedahari + ' Hari' : '-'"></span>
                            </div>
                        </div>

                        <!-- Daftar Item Barang -->
                        <div class="pt-3 border-t border-gray-100">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Item Barang Dipesan</div>
                            <div class="space-y-2">
                                <template x-for="(item, idx) in (poData.items || [])" :key="idx">
                                    <div class="p-2.5 bg-gray-50 rounded-xl text-xs flex items-center justify-between">
                                        <div>
                                            <div class="font-bold text-gray-800" x-text="item.nama_barang"></div>
                                            <div class="text-gray-500 mt-0.5" x-text="'Qty: ' + item.qty + ' ' + (item.satuan || '')"></div>
                                        </div>
                                        <div>
                                            <span x-show="item.ready" class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold text-[10px]">
                                                Ready
                                            </span>
                                            <span x-show="!item.ready" class="px-2 py-0.5 rounded-full bg-gray-200 text-gray-600 font-medium text-[10px]">
                                                Proses
                                            </span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Pengiriman -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                        <div class="flex items-center space-x-2 text-gray-800 font-bold border-b border-gray-100 pb-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                            <span>Informasi Pengiriman</span>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-xs text-gray-500 block">Alamat Tujuan:</span>
                                <span class="font-semibold text-gray-800" x-text="poData.alamat"></span>
                            </div>
                            <div x-show="poData.alamat_lengkap">
                                <span class="text-xs text-gray-500 block">Alamat Lengkap:</span>
                                <span class="text-xs text-gray-700 leading-relaxed" x-text="poData.alamat_lengkap"></span>
                            </div>

                            <!-- Contact Person -->
                            <div class="pt-2 border-t border-gray-50">
                                <span class="text-xs text-gray-500 block mb-1">Contact Person:</span>
                                <template x-for="(c, idx) in (poData.contacts || [])" :key="idx">
                                    <div class="text-xs text-gray-700 flex items-center justify-between py-0.5">
                                        <span class="font-medium" x-text="c.contact"></span>
                                        <span class="text-gray-500 font-mono" x-text="c.no_contact"></span>
                                    </div>
                                </template>
                            </div>

                            <div class="flex justify-between py-1.5 border-t border-gray-50">
                                <span class="text-gray-500">Tanggal Kirim:</span>
                                <span class="font-semibold text-gray-800" x-text="formatDate(poData.deleverydate)"></span>
                            </div>

                            <div class="flex justify-between py-1.5 border-t border-gray-50">
                                <span class="text-gray-500">Ekspedisi:</span>
                                <span class="font-semibold text-gray-800" x-text="poData.ekspedisi || '-'"></span>
                            </div>

                            <div class="flex justify-between py-1.5 border-t border-gray-50">
                                <span class="text-gray-500">No Telp Ekspedisi:</span>
                                <span class="font-semibold text-gray-800 font-mono" x-text="poData.no_ekspedisi || '-'"></span>
                            </div>

                            <div class="flex justify-between py-1.5 border-t border-gray-50 items-center">
                                <span class="text-gray-500">Nomor Resi:</span>
                                <span class="font-bold text-gray-900 font-mono text-base" x-text="poData.noresi || '-'"></span>
                            </div>

                            <!-- Tombol Lacak Paket ↗ -->
                            <div x-show="poData.linkekspedisi" class="pt-2">
                                <a :href="poData.linkekspedisi" 
                                   target="_blank" 
                                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm shadow-sm transition">
                                    <span>Lacak Paket di Website Ekspedisi ↗</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Catatan Keterangan -->
                <div x-show="poData.keterangan" class="bg-amber-50 rounded-2xl border border-amber-200 p-5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-1">Catatan / Keterangan</h4>
                    <p class="text-sm text-amber-900" x-text="poData.keterangan"></p>
                </div>

                <!-- Foto Resi & Foto Barang -->
                <div x-show="poData.fotoresi || poData.foto_barang" class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
                    <h4 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">
                        Dokumentasi Foto Pengiriman
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div x-show="poData.fotoresi" class="space-y-2">
                            <span class="text-xs font-semibold text-gray-600">Foto Resi Pengiriman:</span>
                            <div class="border rounded-xl p-2 bg-gray-50 flex justify-center max-h-80 overflow-hidden">
                                <img :src="poData.fotoresi" alt="Foto Resi" class="max-h-72 object-contain rounded-lg shadow-xs">
                            </div>
                        </div>
                        <div x-show="poData.foto_barang" class="space-y-2">
                            <span class="text-xs font-semibold text-gray-600">Foto Barang:</span>
                            <div class="border rounded-xl p-2 bg-gray-50 flex justify-center max-h-80 overflow-hidden">
                                <img :src="poData.foto_barang" alt="Foto Barang" class="max-h-72 object-contain rounded-lg shadow-xs">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-gray-200 py-6 text-center text-xs text-gray-500">
        <div class="max-w-6xl mx-auto px-4">
            <p class="font-medium text-gray-600">CV. Indomulti Prakarsa Mandiri</p>
            <p class="mt-1">Hak cipta © 2026 Dashboard System. All rights reserved. Version 1.0.0.</p>
        </div>
    </footer>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('trackingApp', (initialPoQuery, initialPoData) => ({
            poNumber: initialPoQuery || '',
            poData: initialPoData || null,
            loading: false,
            errorMessage: '',

            init() {
                if (this.poNumber && !this.poData) {
                    this.searchTracking();
                }
            },

            formatDate(dateStr) {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                if (isNaN(d)) return dateStr;
                return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
            },

            getStatusBadgeClass(status) {
                switch (status) {
                    case 'GAJADI': return 'bg-red-100 text-red-800 border-red-300';
                    case 'PENDING': return 'bg-amber-100 text-amber-800 border-amber-300';
                    case 'PROSES': return 'bg-blue-100 text-blue-800 border-blue-300';
                    case 'SELESAI': return 'bg-emerald-100 text-emerald-800 border-emerald-300';
                    case 'DIKIRIM': return 'bg-purple-100 text-purple-800 border-purple-300';
                    case 'PERSIAPAN_BARANG': return 'bg-orange-100 text-orange-800 border-orange-300';
                    case 'PENGIRIMAN PARCIAL': return 'bg-amber-100 text-amber-800 border-amber-300';
                    case 'PENGIRIMAN LENGKAP': return 'bg-emerald-100 text-emerald-800 border-emerald-300';
                    default: return 'bg-gray-100 text-gray-800 border-gray-300';
                }
            },

            async searchTracking() {
                if (!this.poNumber.trim()) return;

                this.loading = true;
                this.errorMessage = '';
                this.poData = null;

                try {
                    const res = await fetch('/tracking/search', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ po: this.poNumber })
                    });

                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Nomor PO tidak ditemukan.');
                    }

                    this.poData = data;
                    // Update URL without reload
                    const url = new URL(window.location);
                    url.searchParams.set('po', this.poNumber);
                    window.history.pushState({}, '', url);
                } catch (err) {
                    this.errorMessage = err.message;
                } finally {
                    this.loading = false;
                }
            }
        }));
    });
    </script>
</body>
</html>
