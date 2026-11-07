@extends('layouts.app')

@section('title', 'Delivery - Status Barang & PO')

@section('content')
<div x-data="deliveryApp({{ Js::from($pos) }}, {{ Js::from($ekspedisis) }})" class="space-y-6">
    <!-- Banner Welcome -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#79B440] via-[#6a9d38] to-[#517a29] p-6 sm:p-8 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider backdrop-blur-xs mb-3">
                    Portal Delivery
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, Delivery
                </h1>
                <p class="mt-1 text-sm sm:text-base text-lime-100 font-medium">
                    Kelola Informasi Pengiriman Purchase Order
                </p>
            </div>
            <div class="shrink-0">
                <button @click="openEkspedisiModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#6a9d38] hover:bg-gray-100 font-bold text-sm shadow-md transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Kelola Ekspedisi</span>
                </button>
            </div>
        </div>
        <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white p-4 rounded-xl shadow-xs border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-96">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Cari No PO atau Customer..."
                   class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 text-sm outline-hidden transition">
        </div>

        <div class="text-xs text-gray-500 font-medium self-end sm:self-center">
            Total: <span class="font-bold text-gray-800" x-text="filteredPos.length"></span> PO
        </div>
    </div>

    <!-- Compact Table -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 select-none">
                    <tr>
                        <th class="py-3 px-4">No PO</th>
                        <th class="py-3 px-4">Nama Customer</th>
                        <th class="py-3 px-4">Tgl Terbit DO</th>
                        <th class="py-3 px-4">Ekspedisi / Resi</th>
                        <th class="py-3 px-4 text-center">Status Pengiriman</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <template x-for="po in filteredPos" :key="po.id">
                        <tr @click="openDeliveryModal(po)" 
                            class="hover:bg-lime-50/50 cursor-pointer transition-colors group">
                            <td class="py-3.5 px-4 font-bold text-gray-900 group-hover:text-[#79B440] transition-colors" x-text="po.po"></td>
                            <td class="py-3.5 px-4 text-gray-800 font-medium" x-text="po.kostumer"></td>
                            <td class="py-3.5 px-4 text-gray-600" x-text="formatDate(po.tgl_terbit_do)"></td>
                            <td class="py-3.5 px-4 text-gray-700">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-medium" x-text="po.ekspedisi || '-'"></span>
                                    <span x-show="po.noresi" class="text-xs font-mono text-gray-500" x-text="'(' + po.noresi + ')'"></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-3 py-1 rounded-full text-xs font-bold border"
                                      :class="getStatusBadgeClass(po.status)"
                                      x-text="po.status || '-'">
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-[#79B440] group-hover:underline">
                                    <span>Edit Pengiriman</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredPos.length === 0">
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            Tidak ada data Purchase Order.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Informasi Pengiriman -->
    <div x-cloak 
         x-show="deliveryModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="closeDeliveryModal()">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="closeDeliveryModal()"></div>

        <!-- Modal Box -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl overflow-hidden my-8"
                 @click.stop>
                <!-- Header -->
                <div class="bg-gradient-to-r from-[#79B440] to-[#517a29] px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">Edit Informasi Pengiriman</h3>
                        <p class="text-xs text-lime-100">
                            No PO: <span class="font-bold underline" x-text="selectedPo?.po"></span> | Customer: <span x-text="selectedPo?.kostumer"></span>
                        </p>
                    </div>
                    <button @click="closeDeliveryModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="saveDeliveryInfo()" class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                    <!-- Status Pengiriman Dropdown -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                            Status Pengiriman <span class="text-red-500">*</span>
                        </label>
                        <select x-model="form.status" 
                                required 
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 outline-hidden font-semibold bg-white">
                            <option value="PENGIRIMAN PARCIAL">PENGIRIMAN PARCIAL</option>
                            <option value="PENGIRIMAN LENGKAP">PENGIRIMAN LENGKAP</option>
                            <option value="DIKIRIM">DIKIRIM</option>
                            <option value="PROSES">PROSES</option>
                        </select>
                    </div>

                    <!-- Ekspedisi & Link Ekspedisi (Auto-filled) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Pilih Ekspedisi
                            </label>
                            <select x-model="form.ekspedisi" 
                                    @change="onEkspedisiChange()"
                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 outline-hidden bg-white">
                                <option value="">-- Pilih Master Ekspedisi --</option>
                                <template x-for="exp in ekspedisis" :key="exp.id">
                                    <option :value="exp.nama_ekspedisi" x-text="exp.nama_ekspedisi"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Link Tracking Ekspedisi
                            </label>
                            <input type="text" 
                                   x-model="form.linkekspedisi" 
                                   placeholder="https://..."
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 outline-hidden">
                        </div>
                    </div>

                    <!-- Tanggal Kirim, No Resi, No Telp Ekspedisi -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Delivery Date (Tgl Kirim)
                            </label>
                            <input type="date" 
                                   x-model="form.deleverydate" 
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 outline-hidden">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                Nomor Resi
                            </label>
                            <input type="text" 
                                   x-model="form.noresi" 
                                   placeholder="cth: JT123456789"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 outline-hidden font-mono">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                                No Telp / WA Ekspedisi
                            </label>
                            <input type="text" 
                                   x-model="form.no_ekspedisi" 
                                   placeholder="cth: 021-80661888"
                                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] focus:ring-2 focus:ring-[#79B440]/20 outline-hidden">
                        </div>
                    </div>

                    <!-- Upload / Tangkap Foto Resi (Multi-opsi: File, Mobile Camera, Webcam) -->
                    <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                            Upload / Tangkap Foto Resi Pengiriman
                        </label>

                        <!-- Action Buttons Multi-opsi -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- 📁 File Upload Button -->
                            <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-100 transition shadow-2xs">
                                <span>📁 File Lokal</span>
                                <input type="file" 
                                       accept="image/*" 
                                       @change="handleFileUpload($event)"
                                       class="hidden">
                            </label>

                            <!-- 📷 Mobile Camera Button -->
                            <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-100 transition shadow-2xs">
                                <span>📷 Kamera HP</span>
                                <input type="file" 
                                       accept="image/*" 
                                       capture="environment" 
                                       @change="handleFileUpload($event)"
                                       class="hidden">
                            </label>

                            <!-- 🎥 Webcam Toggle Button -->
                            <button type="button" 
                                    @click="toggleWebcam()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-100 transition shadow-2xs">
                                <span>🎥 Webcam Live</span>
                            </button>
                        </div>

                        <!-- Webcam Live Preview Area -->
                        <div x-show="webcamActive" x-cloak class="space-y-3 p-3 bg-black/90 rounded-xl text-center">
                            <video x-ref="videoElement" 
                                   autoplay 
                                   playsinline 
                                   class="mx-auto rounded-lg max-h-64 w-auto bg-black border border-gray-700"></video>
                            <canvas x-ref="canvasElement" class="hidden"></canvas>
                            <div class="flex items-center justify-center gap-3">
                                <button type="button" 
                                        @click="captureWebcam()" 
                                        class="px-4 py-2 bg-[#79B440] text-white font-bold rounded-lg text-xs hover:bg-[#6a9d38] shadow-md">
                                    📸 Ambil Foto Sekarang
                                </button>
                                <button type="button" 
                                        @click="stopWebcam()" 
                                        class="px-4 py-2 bg-gray-600 text-white font-semibold rounded-lg text-xs hover:bg-gray-700">
                                    Tutup Webcam
                                </button>
                            </div>
                        </div>

                        <!-- Alternatif URL Teks -->
                        <div>
                            <label class="block text-[11px] text-gray-500 mb-1">Atau masukkan URL / Nama File Gambar langsung:</label>
                            <input type="text" 
                                   x-model="form.fotoresi" 
                                   placeholder="https://... atau nama-file.jpg"
                                   class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:border-[#79B440] outline-hidden bg-white">
                        </div>

                        <!-- Image Preview -->
                        <div x-show="form.fotoresi" x-cloak class="mt-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-semibold text-gray-700">Preview Foto Resi:</span>
                                <button type="button" 
                                        @click="form.fotoresi = ''" 
                                        class="text-xs text-red-500 hover:text-red-700 font-semibold">
                                    ✕ Hapus Foto
                                </button>
                            </div>
                            <div class="border rounded-xl p-2 bg-white flex justify-center max-h-64 overflow-hidden">
                                <img :src="form.fotoresi" 
                                     alt="Preview Resi" 
                                     class="max-h-60 rounded-lg object-contain">
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                @click="closeDeliveryModal()" 
                                class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="saving"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[#79B440] hover:bg-[#6a9d38] shadow-md transition disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Simpan Informasi Pengiriman</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Kelola Master Ekspedisi -->
    <div x-cloak 
         x-show="ekspedisiModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="closeEkspedisiModal()">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="closeEkspedisiModal()"></div>

        <!-- Modal Box -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden my-8"
                 @click.stop>
                <!-- Header -->
                <div class="bg-gray-800 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold">Kelola Master Data Ekspedisi</h3>
                        <p class="text-xs text-gray-300">Tambah atau hapus daftar ekspedisi beserta link tracking bawaan.</p>
                    </div>
                    <button @click="closeEkspedisiModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                    <!-- Form Tambah Ekspedisi -->
                    <form @submit.prevent="addEkspedisi()" class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700">Tambah Ekspedisi Baru</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" 
                                   x-model="newEkspedisi.nama_ekspedisi" 
                                   required 
                                   placeholder="Nama Ekspedisi (cth: JNE)"
                                   class="px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] outline-hidden bg-white">
                            <input type="text" 
                                   x-model="newEkspedisi.link_ekspedisi" 
                                   placeholder="Link Tracking (cth: https://jne.co.id/tracking)"
                                   class="px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#79B440] outline-hidden bg-white">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    :disabled="savingEkspedisi"
                                    class="px-4 py-2 bg-[#79B440] hover:bg-[#6a9d38] text-white text-xs font-bold rounded-lg shadow-sm transition disabled:opacity-50">
                                + Simpan Ekspedisi
                            </button>
                        </div>
                    </form>

                    <!-- List Ekspedisi -->
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Daftar Ekspedisi Tersedia</h4>
                        <div class="border rounded-xl divide-y divide-gray-200 overflow-hidden">
                            <template x-for="exp in ekspedisis" :key="exp.id">
                                <div class="p-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                                    <div>
                                        <div class="font-bold text-sm text-gray-900" x-text="exp.nama_ekspedisi"></div>
                                        <div class="text-xs text-gray-500 truncate max-w-sm" x-text="exp.link_ekspedisi || 'Tidak ada link tracking bawaan'"></div>
                                    </div>
                                    <button type="button" 
                                            @click="deleteEkspedisi(exp.id, exp.nama_ekspedisi)" 
                                            class="text-xs text-red-600 hover:text-red-800 p-1.5 rounded-lg hover:bg-red-50 font-semibold transition">
                                        Hapus
                                    </button>
                                </div>
                            </template>
                            <div x-show="ekspedisis.length === 0" class="p-6 text-center text-xs text-gray-400">
                                Belum ada ekspedisi terdaftar.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 border-t border-gray-200 flex justify-end">
                    <button type="button" 
                            @click="closeEkspedisiModal()" 
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-200 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('deliveryApp', (initialPos, initialEkspedisis) => ({
        pos: initialPos || [],
        ekspedisis: initialEkspedisis || [],
        searchQuery: '',
        deliveryModalOpen: false,
        ekspedisiModalOpen: false,
        selectedPo: null,
        saving: false,
        savingEkspedisi: false,
        webcamActive: false,
        stream: null,

        form: {
            status: 'PENGIRIMAN PARCIAL',
            ekspedisi: '',
            linkekspedisi: '',
            deleverydate: '',
            noresi: '',
            no_ekspedisi: '',
            fotoresi: ''
        },

        newEkspedisi: {
            nama_ekspedisi: '',
            link_ekspedisi: ''
        },

        get filteredPos() {
            if (!this.searchQuery.trim()) return this.pos;
            const q = this.searchQuery.toLowerCase();
            return this.pos.filter(p => {
                const poMatch = (p.po || '').toLowerCase().includes(q);
                const custMatch = (p.kostumer || '').toLowerCase().includes(q);
                return poMatch || custMatch;
            });
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d)) return dateStr;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        getStatusBadgeClass(status) {
            switch (status) {
                case 'PENGIRIMAN PARCIAL': return 'bg-amber-100 text-amber-700 border-amber-200';
                case 'PENGIRIMAN LENGKAP': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
                case 'DIKIRIM': return 'bg-purple-100 text-purple-700 border-purple-200';
                case 'PROSES': return 'bg-blue-100 text-blue-700 border-blue-200';
                default: return 'bg-gray-100 text-gray-700 border-gray-200';
            }
        },

        async openDeliveryModal(po) {
            this.selectedPo = po;
            try {
                const res = await fetch(`/po/${po.id}`);
                const data = await res.json();
                this.form = {
                    status: data.status && (data.status.includes('PENGIRIMAN') || data.status === 'DIKIRIM') ? data.status : 'PENGIRIMAN PARCIAL',
                    ekspedisi: data.ekspedisi || '',
                    linkekspedisi: data.linkekspedisi || '',
                    deleverydate: data.deleverydate || new Date().toISOString().split('T')[0],
                    noresi: data.noresi || '',
                    no_ekspedisi: data.no_ekspedisi || '',
                    fotoresi: data.fotoresi || ''
                };
                this.deliveryModalOpen = true;
            } catch (err) {
                showToast('Gagal memuat detail pengiriman', 'error');
            }
        },

        closeDeliveryModal() {
            this.stopWebcam();
            this.deliveryModalOpen = false;
            this.selectedPo = null;
        },

        onEkspedisiChange() {
            const exp = this.ekspedisis.find(e => e.nama_ekspedisi === this.form.ekspedisi);
            if (exp && exp.link_ekspedisi) {
                this.form.linkekspedisi = exp.link_ekspedisi;
            }
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                this.form.fotoresi = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        toggleWebcam() {
            if (this.webcamActive) {
                this.stopWebcam();
            } else {
                this.startWebcam();
            }
        },

        async startWebcam() {
            try {
                this.webcamActive = true;
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' }, 
                    audio: false 
                });
                this.stream = stream;
                this.$nextTick(() => {
                    if (this.$refs.videoElement) {
                        this.$refs.videoElement.srcObject = stream;
                    }
                });
            } catch (err) {
                showToast('Tidak dapat mengakses webcam / kamera: ' + err.message, 'error');
                this.webcamActive = false;
            }
        },

        captureWebcam() {
            const video = this.$refs.videoElement;
            const canvas = this.$refs.canvasElement;
            if (!video || !canvas) return;

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            this.form.fotoresi = dataUrl;
            this.stopWebcam();
            showToast('Foto berhasil diambil dari kamera!', 'success');
        },

        stopWebcam() {
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            this.webcamActive = false;
        },

        async saveDeliveryInfo() {
            if (!this.selectedPo) return;
            this.saving = true;

            try {
                const res = await fetch(`/po/${this.selectedPo.id}/delivery`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyimpan pengiriman');

                showToast(data.message || 'Informasi pengiriman berhasil diperbarui!', 'success');
                this.closeDeliveryModal();

                // Refresh table
                const refreshRes = await fetch('/po');
                this.pos = await refreshRes.json();
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                this.saving = false;
            }
        },

        openEkspedisiModal() {
            this.ekspedisiModalOpen = true;
        },

        closeEkspedisiModal() {
            this.ekspedisiModalOpen = false;
        },

        async addEkspedisi() {
            this.savingEkspedisi = true;
            try {
                const res = await fetch('/ekspedisi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.newEkspedisi)
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menambah ekspedisi');

                showToast(data.message || 'Ekspedisi berhasil ditambahkan', 'success');
                this.newEkspedisi = { nama_ekspedisi: '', link_ekspedisi: '' };

                // Refresh ekspedisi
                const expRes = await fetch('/ekspedisi');
                this.ekspedisis = await expRes.json();
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                this.savingEkspedisi = false;
            }
        },

        async deleteEkspedisi(id, nama) {
            if (!confirm(`Hapus ekspedisi ${nama}?`)) return;

            try {
                const res = await fetch(`/ekspedisi/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menghapus');

                showToast(data.message || 'Ekspedisi berhasil dihapus', 'success');
                this.ekspedisis = this.ekspedisis.filter(e => e.id !== id);
            } catch (err) {
                showToast(err.message, 'error');
            }
        }
    }));
});
</script>
@endpush
