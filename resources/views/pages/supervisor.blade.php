@extends('layouts.app')

@section('title', 'Supervisor Gudang - Status Barang & PO')

@section('content')
    <div x-data="supervisorApp({{ Js::from($pos) }})" class="space-y-6">
        <!-- Banner Welcome -->
        <div
            class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#1E93AB] via-[#177a93] to-[#0f5b6e] p-6 sm:p-8 text-white shadow-lg">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider backdrop-blur-xs mb-3">
                        Portal Supervisor Gudang
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, Supervisor Gudang
                    </h1>
                    <p class="mt-1 text-sm sm:text-base text-cyan-100 font-medium">
                        Kelola Status dan Keterangan Purchase Order
                    </p>
                </div>
                <div class="text-xs bg-white/10 backdrop-blur-xs px-4 py-3 rounded-xl border border-white/20 max-w-xs">
                    💡 <span class="font-semibold">Petunjuk:</span> Klik salah satu baris PO pada tabel untuk memeriksa
                    checklist item ready dan memperbarui status pesanan.
                </div>
            </div>
            <div class="absolute -right-10 -bottom-10 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        </div>

        <!-- Search & Filter Bar -->
        <div
            class="bg-white p-4 rounded-xl shadow-xs border border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="relative w-full sm:w-96">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" x-model="searchQuery" placeholder="Cari No PO atau Customer..."
                    class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-[#1E93AB] focus:ring-2 focus:ring-[#1E93AB]/20 text-sm outline-hidden transition">
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
                            <th class="py-3 px-4 text-center">Status Saat Ini</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="po in filteredPos" :key="po.id">
                            <tr @click="openEditModal(po)"
                                class="hover:bg-cyan-50/50 cursor-pointer transition-colors group">
                                <td class="py-3.5 px-4 font-bold text-gray-900 group-hover:text-[#1E93AB] transition-colors"
                                    x-text="po.po"></td>
                                <td class="py-3.5 px-4 text-gray-800 font-medium" x-text="po.kostumer"></td>
                                <td class="py-3.5 px-4 text-gray-600" x-text="formatDate(po.tgl_terbit_do)"></td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border"
                                        :class="getStatusBadgeClass(po.status)" x-text="po.status || '-'">
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-[#1E93AB] group-hover:underline">
                                        <span>Kelola Status</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredPos.length === 0">
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                Tidak ada data Purchase Order.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Edit Status & Checklist Supervisor -->
        <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="closeModal()">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="closeModal()"></div>

            <!-- Modal Box -->
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl overflow-hidden my-8" @click.stop>
                    <!-- Header -->
                    <div
                        class="bg-gradient-to-r from-[#1E93AB] to-[#177a93] px-6 py-4 text-white flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold">Kelola Status & Checklist Barang PO</h3>
                            <p class="text-xs text-cyan-100">
                                No PO: <span class="font-bold underline" x-text="selectedPo?.po"></span> | Customer: <span
                                    x-text="selectedPo?.kostumer"></span>
                            </p>
                        </div>
                        <button @click="closeModal()"
                            class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Form Content -->
                    <form @submit.prevent="sendItemsToDelivery()" class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                        <!-- Dropdown Status & Keterangan -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Status Dropdown -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                        Pilih Status PO <span class="text-red-500">*</span>
                                    </label>
                                    <select x-model="form.status" required
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#1E93AB] focus:ring-2 focus:ring-[#1E93AB]/20 outline-hidden bg-white font-semibold">
                                        <option value="PERSIAPAN_BARANG">Persiapan Barang</option>
                                        <option value="PACKING"> Packing</option>
                                        <option value="INDENT">Indent</option>
                                        <option value="PROSES">Dalam Proses</option>
                                        <option value="PARCIAL">Pengriman Parcial</option>
                                        <option value="LENGKAP">Pengiriman Lengkap</option>
                                    </select>
                                </div>

                                <!-- Keterangan Textarea -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                        Keterangan Supervisor
                                    </label>
                                    <textarea x-model="form.keterangan" rows="2" placeholder="Catatan status/kondisi barang gudang..."
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#1E93AB] focus:ring-2 focus:ring-[#1E93AB]/20 outline-hidden bg-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Checklist Item Ready & Dynamic Items -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Checklist Item Ready & Kelola Barang</h4>
                                    <p class="text-xs text-gray-500">Centang checkbox jika barang sudah siap/ready di
                                        gudang.</p>
                                </div>
                            </div>

                            <!-- List Items -->
                            <div class="space-y-3">
                                <template x-for="(item, idx) in form.items" :key="idx">
                                    <div class="p-3.5 rounded-xl border transition-all duration-200"
                                        :class="item.ready ? 'bg-emerald-50/60 border-emerald-300 shadow-xs' :
                                            'bg-gray-50 border-gray-200'">

                                        <!-- Header Row: Checkbox Barang Ready -->
                                        <div
                                            class="flex items-center justify-between pb-2 mb-2 border-b border-gray-200/80">
                                            <label class="flex items-center space-x-2.5 select-none"
                                                :class="item.ready ? 'cursor-not-allowed' : 'cursor-pointer'">
                                                <input type="checkbox" x-model="item.ready" :disabled="item.ready"
                                                    :title="item.ready ? 'Barang yang sudah dikirim tidak dapat diubah' :
                                                        'Tandai barang siap'"
                                                    class="w-4 h-4 text-[#1E93AB] rounded-sm focus:ring-[#1E93AB] border-gray-300 disabled:opacity-70">
                                                <span class="text-xs font-bold"
                                                    :class="item.ready ? 'text-emerald-700' : 'text-gray-700'">
                                                    <span x-show="item.ready">✓ Barang Sudah Dikirim</span>
                                                    <span x-show="!item.ready">○ Belum Ready</span>
                                                </span>
                                            </label>
                                        </div>

                                        <!-- Item Input Fields -->
                                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                            <!-- Nama Barang -->
                                            <div class="sm:col-span-5">
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nama
                                                    Barang</label>
                                                <input type="text" x-model="item.nama_barang" required
                                                    placeholder="Nama barang..."
                                                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:border-[#1E93AB] outline-hidden bg-white">
                                            </div>

                                            <!-- Qty -->
                                            <div class="sm:col-span-2">
                                                <label
                                                    class="block text-[11px] font-semibold text-gray-600 mb-1">Qty</label>
                                                <input type="number" min="0" step="any"
                                                    x-model.number="item.qty" @input="recalculateItem(item)" required
                                                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:border-[#1E93AB] outline-hidden bg-white">
                                            </div>

                                            <!-- Satuan -->
                                            <div class="sm:col-span-2">
                                                <label
                                                    class="block text-[11px] font-semibold text-gray-600 mb-1">Satuan</label>
                                                <input type="text" x-model="item.satuan" placeholder="PCS"
                                                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:border-[#1E93AB] outline-hidden bg-white uppercase">
                                            </div>

                                            <!-- Harga -->
                                            <div class="sm:col-span-3">
                                                <label class="block text-[11px] font-semibold text-gray-600 mb-1">Harga
                                                    Satuan</label>
                                                <input type="text" :value="formatRupiahInput(item.harga)"
                                                    @input="onItemPriceChange(item, $event.target.value)"
                                                    placeholder="Rp 0"
                                                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 focus:border-[#1E93AB] outline-hidden bg-white">
                                            </div>
                                        </div>

                                        <!-- Item Calculations -->
                                        <div
                                            class="flex items-center justify-between text-xs pt-2 mt-2 border-t border-gray-200 text-gray-500">
                                            <span>Jumlah: <strong class="text-gray-800"
                                                    x-text="formatRupiah(item.jumlah)"></strong></span>
                                            <span>PPN 11%: <strong class="text-gray-800"
                                                    x-text="formatRupiah(item.ppn)"></strong></span>
                                            <span>Total: <strong class="text-[#1E93AB]"
                                                    x-text="formatRupiah(item.total)"></strong></span>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="form.items.length === 0" class="text-xs text-gray-400 italic">
                                    Belum ada item. Klik 'Tambah Item Baru'.
                                </div>
                            </div>

                            <!-- Grand Total Summary -->
                            <div
                                class="p-3 bg-gray-100 rounded-xl flex items-center justify-between text-xs sm:text-sm font-semibold text-gray-700">
                                <div>
                                    Item Ready: <span class="text-emerald-600" x-text="readyItemsCount"></span> / <span
                                        x-text="form.items.length"></span> Item
                                </div>
                                <div>
                                    Grand Total: <span class="text-[#1E93AB] text-base font-bold"
                                        x-text="formatRupiah(grandTotal)"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <button type="button" @click="closeModal()"
                                class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">
                                Tutup
                            </button>
                            <button type="submit" :disabled="saving"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[#1E93AB] hover:bg-[#177a93] shadow-md transition disabled:opacity-50 flex items-center gap-2">
                                <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Kirim ke Delivery</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('supervisorApp', (initialPos) => ({
                pos: initialPos || [],
                searchQuery: '',
                modalOpen: false,
                selectedPo: null,
                saving: false,

                form: {
                    status: 'PERSIAPAN_BARANG',
                    keterangan: '',
                    items: []
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
                    return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                },

                getStatusBadgeClass(status) {
                    switch (status) {
                        case 'PERSIAPAN_BARANG':
                            return 'bg-orange-100 text-orange-700 border-orange-200';
                        case 'PACKING':
                            return 'bg-indigo-100 text-indigo-700 border-indigo-200';
                        case 'INDENT':
                            return 'bg-amber-100 text-amber-700 border-amber-200';
                        case 'PROSES':
                            return 'bg-blue-100 text-blue-700 border-blue-200';
                        case 'SELESAI':
                            return 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        case 'DIKIRIM':
                            return 'bg-purple-100 text-purple-700 border-purple-200';
                        case 'GAJADI':
                            return 'bg-red-100 text-red-700 border-red-200';
                        default:
                            return 'bg-gray-100 text-gray-700 border-gray-200';
                    }
                },

                async openEditModal(po) {
                    this.selectedPo = po;
                    try {
                        const res = await fetch(`/po/${po.id}`);
                        const data = await res.json();
                        this.form = {
                            status: data.status && data.status !== '-' ? data.status :
                                'PERSIAPAN_BARANG',
                            keterangan: data.keterangan || '',
                            items: data.items && data.items.length ? JSON.parse(JSON.stringify(
                                data.items)) : []
                        };
                        this.modalOpen = true;
                    } catch (err) {
                        showToast('Gagal memuat detail PO', 'error');
                    }
                },

                closeModal() {
                    this.modalOpen = false;
                    this.selectedPo = null;
                },

                formatRupiahInput(val) {
                    if (!val) return 'Rp 0';
                    return 'Rp ' + Number(val).toLocaleString('id-ID');
                },

                onItemPriceChange(item, rawVal) {
                    item.harga = parseRupiah(rawVal);
                    this.recalculateItem(item);
                },

                recalculateItem(item) {
                    const qty = Number(item.qty) || 0;
                    const harga = Number(item.harga) || 0;
                    item.jumlah = qty * harga;
                    item.ppn = Math.round(item.jumlah * 0.11);
                    item.total = item.jumlah + item.ppn;
                },

                get readyItemsCount() {
                    return (this.form.items || []).filter(it => !!it.ready).length;
                },

                get grandTotal() {
                    return (this.form.items || []).reduce((acc, it) => acc + (Number(it.total) ||
                        0), 0);
                },

                async sendItemsToDelivery() {
                    if (!this.selectedPo) return;
                    this.saving = true;

                    try {
                        const res = await fetch(`/po/${this.selectedPo.id}/send-to-delivery`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const data = await res.json();
                        if (!res.ok) throw new Error(data.message ||
                            'Gagal mengirim barang ke Delivery');

                        showToast(data.message || 'Barang berhasil dikirim ke Delivery!',
                        'success');
                        this.closeModal();

                        // Refresh table
                        const refreshRes = await fetch('/po');
                        this.pos = await refreshRes.json();
                    } catch (err) {
                        showToast(err.message, 'error');
                    } finally {
                        this.saving = false;
                    }
                }
            }));
        });
    </script>
@endpush
