@extends('layouts.app')

@section('title', 'Admin Gudang - Status Barang & PO')

@section('content')
<div x-data="adminApp({{ Js::from($pos) }})" class="space-y-6">
    <!-- Banner Welcome -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#E62727] via-[#d01f1f] to-[#b31414] p-6 sm:p-8 text-white shadow-lg">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider backdrop-blur-xs mb-3">
                    Portal Admin Gudang
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Selamat Datang, Admin Gudang
                </h1>
                <p class="mt-1 text-sm sm:text-base text-red-100 font-medium">
                    Kelola Purchase Order dan Pengiriman Barang
                </p>
            </div>
            <div class="shrink-0 flex items-center gap-3">
                <a href="{{ route('po.excel.all') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Print All Data</span>
                </a>
                <button @click="openCreateModal()" 
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-[#E62727] hover:bg-gray-100 font-bold text-sm shadow-md transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah PO</span>
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
                   placeholder="Cari PO, customer, alamat, item, ekspedisi..."
                   class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 text-sm outline-hidden transition">
        </div>

        <div class="text-xs text-gray-500 font-medium self-end sm:self-center">
            Total: <span class="font-bold text-gray-800" x-text="filteredPos.length"></span> PO ditemukan
        </div>
    </div>

    <!-- Table PO -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 select-none">
                    <tr>
                        <th @click="sortBy('po')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>PO</span>
                                <span class="text-[10px]" x-html="sortIcon('po')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('tgl_po')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>Tgl PO</span>
                                <span class="text-[10px]" x-html="sortIcon('tgl_po')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('tgl_terima_po')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>Tgl Terima PO</span>
                                <span class="text-[10px]" x-html="sortIcon('tgl_terima_po')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('tgl_terbit_do')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>Tgl Terbit DO</span>
                                <span class="text-[10px]" x-html="sortIcon('tgl_terbit_do')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('deleverydate')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>Delivery Date</span>
                                <span class="text-[10px]" x-html="sortIcon('deleverydate')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('jedahari')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">
                                <span>Jeda Hari</span>
                                <span class="text-[10px]" x-html="sortIcon('jedahari')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('kostumer')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap min-w-[140px]">
                            <div class="flex items-center gap-1">
                                <span>Customer</span>
                                <span class="text-[10px]" x-html="sortIcon('kostumer')"></span>
                            </div>
                        </th>
                        <th class="py-3 px-3 min-w-[160px]">Alamat</th>
                        <th class="py-3 px-3 min-w-[220px]">Items</th>
                        <th @click="sortBy('ekspedisi')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>Ekspedisi</span>
                                <span class="text-[10px]" x-html="sortIcon('ekspedisi')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('noresi')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>No Resi</span>
                                <span class="text-[10px]" x-html="sortIcon('noresi')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('do')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <span>DO</span>
                                <span class="text-[10px]" x-html="sortIcon('do')"></span>
                            </div>
                        </th>
                        <th @click="sortBy('status')" class="py-3 px-3 cursor-pointer hover:bg-gray-100 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">
                                <span>Status</span>
                                <span class="text-[10px]" x-html="sortIcon('status')"></span>
                            </div>
                        </th>
                        <th class="py-3 px-3 min-w-[130px]">Keterangan</th>
                        <th class="py-3 px-3 text-center whitespace-nowrap sticky right-0 bg-gray-50 border-l border-gray-200">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-normal">
                    <template x-for="po in filteredPos" :key="po.id">
                        <tr class="hover:bg-red-50/40 transition-colors">
                            <td class="py-3 px-3 font-semibold text-gray-900 whitespace-nowrap" x-text="po.po"></td>
                            <td class="py-3 px-3 text-gray-600 whitespace-nowrap" x-text="formatDate(po.tgl_po)"></td>
                            <td class="py-3 px-3 text-gray-600 whitespace-nowrap" x-text="formatDate(po.tgl_terima_po)"></td>
                            <td class="py-3 px-3 text-gray-600 whitespace-nowrap" x-text="formatDate(po.tgl_terbit_do)"></td>
                            <td class="py-3 px-3 text-gray-600 whitespace-nowrap" x-text="formatDate(po.deleverydate)"></td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span x-show="po.jedahari !== null" 
                                      class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold"
                                      :class="po.jedahari > 5 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800'"
                                      x-text="po.jedahari + ' hr'">
                                </span>
                                <span x-show="po.jedahari === null" class="text-gray-400">-</span>
                            </td>
                            <td class="py-3 px-3 font-medium text-gray-800" x-text="po.kostumer"></td>
                            <td class="py-3 px-3 text-gray-600 max-w-xs truncate" :title="po.alamat" x-text="po.alamat"></td>
                            <td class="py-3 px-3">
                                <div class="space-y-1 max-w-xs">
                                    <template x-for="(item, idx) in (po.items || [])" :key="idx">
                                        <div class="text-[11px] bg-gray-50 p-1.5 rounded-md border border-gray-100 leading-tight">
                                            <div class="font-medium text-gray-900" x-text="item.nama_barang"></div>
                                            <div class="text-gray-500 flex items-center justify-between mt-0.5">
                                                <span x-text="item.qty + ' ' + (item.satuan || '')"></span>
                                                <span class="font-semibold text-gray-700" x-text="formatRupiah(item.total)"></span>
                                            </div>
                                        </div>
                                    </template>
                                    <div x-show="!po.items || po.items.length === 0" class="text-gray-400 italic">
                                        Tidak ada item
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-gray-700 whitespace-nowrap" x-text="po.ekspedisi || '-'"></td>
                            <td class="py-3 px-3 text-gray-700 whitespace-nowrap font-mono text-[11px]" x-text="po.noresi || '-'"></td>
                            <td class="py-3 px-3 text-gray-700 whitespace-nowrap" x-text="po.do || '-'"></td>
                            <td class="py-3 px-3 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                      :class="getStatusBadgeClass(po.status)"
                                      x-text="po.status || '-'">
                                </span>
                            </td>
                            <td class="py-3 px-3 text-gray-500 max-w-xs truncate" :title="po.keterangan" x-text="po.keterangan || '-'"></td>
                            <td class="py-3 px-3 text-center whitespace-nowrap sticky right-0 bg-white border-l border-gray-200">
                                <div class="flex items-center justify-center gap-1.5">
                                    <!-- Edit Button -->
                                    <button @click="openEditModal(po)" 
                                            title="Edit PO"
                                            class="p-1.5 rounded-md bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <!-- Tracking Link Button -->
                                    <template x-if="po.linkekspedisi">
                                        <a :href="po.linkekspedisi" 
                                           target="_blank" 
                                           title="Lacak Ekspedisi"
                                           class="p-1.5 rounded-md bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                        </a>
                                    </template>

                                    <!-- Print DO Excel Button -->
                                    <a :href="'/po/' + po.id + '/excel'" 
                                       title="Print DO Excel"
                                       class="p-1.5 rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>

                                    <!-- Delete Button -->
                                    <button @click="deletePo(po.id, po.po)" 
                                            title="Hapus PO"
                                            class="p-1.5 rounded-md bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredPos.length === 0">
                        <td colspan="15" class="py-12 text-center text-gray-400">
                            Tidak ada data Purchase Order yang cocok.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form Tambah / Edit PO -->
    <div x-cloak 
         x-show="modalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto"
         @keydown.escape.window="closeModal()">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="closeModal()"></div>

        <!-- Modal Box -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="relative w-full max-w-4xl rounded-2xl bg-white shadow-2xl overflow-hidden my-8"
                 @click.stop>
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-[#E62727] to-[#b31414] px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold" x-text="isEdit ? 'Edit Purchase Order' : 'Tambah Purchase Order Baru'"></h3>
                        <p class="text-xs text-red-100" x-text="isEdit ? 'Perbarui detail data PO dan barang' : 'Isi form di bawah untuk membuat PO baru'"></p>
                    </div>
                    <button @click="closeModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form Content -->
                <form @submit.prevent="savePo()" class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                    <!-- Section 1: Informasi PO -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 border-b border-gray-200 pb-1">
                            1. Informasi Utama Purchase Order
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Nomor PO -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Nomor PO <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.po" 
                                       required 
                                       placeholder="cth: PO-2026-001"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>

                            <!-- Customer -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Customer / Instansi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.kostumer" 
                                       required 
                                       placeholder="cth: PT. Maju Bersama"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>

                            <!-- Nomor DO -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor DO</label>
                                <input type="text" 
                                       x-model="form.do" 
                                       placeholder="cth: DO-2026-0089"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>

                            <!-- Jeda Hari (Disabled / Auto calculated) -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Jeda Hari <span class="text-gray-400 text-[10px]">(Auto)</span>
                                </label>
                                <input type="text" 
                                       :value="form.jedahari !== null && form.jedahari !== '' ? form.jedahari + ' Hari' : '-'" 
                                       disabled
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 bg-gray-100 text-gray-600 font-semibold cursor-not-allowed">
                            </div>
                        </div>

                        <!-- Tanggal Dates -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal PO</label>
                                <input type="date" 
                                       x-model="form.tgl_po" 
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Terima PO</label>
                                <input type="date" 
                                       x-model="form.tgl_terima_po" 
                                       @change="recalcJedaHari()"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Terbit DO</label>
                                <input type="date" 
                                       x-model="form.tgl_terbit_do" 
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Delivery Date</label>
                                <input type="date" 
                                       x-model="form.deleverydate" 
                                       @change="recalcJedaHari()"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>
                        </div>

                        <!-- Alamat Singkat & Alamat Lengkap -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Alamat Singkat <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="form.alamat" 
                                       required 
                                       placeholder="cth: Cikarang, Jawa Barat"
                                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">
                                    Alamat Lengkap <span class="text-red-500">*</span>
                                </label>
                                <textarea x-model="form.alamat_lengkap" 
                                          required 
                                          rows="2" 
                                          placeholder="Alamat detail tujuan pengiriman..."
                                          class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Kontak Person (Dinamis) -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                2. Kontak Person Dinamis
                            </h4>
                            <button type="button" 
                                    @click="addContact()" 
                                    class="text-xs font-semibold text-[#E62727] hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Tambah Kontak</span>
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(c, idx) in form.contacts" :key="idx">
                                <div class="flex items-center gap-2">
                                    <input type="text" 
                                           x-model="c.contact" 
                                           placeholder="Nama Kontak (cth: Bpk. Hendra)"
                                           class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                                    <input type="text" 
                                           x-model="c.no_contact" 
                                           placeholder="No Telp / WhatsApp (cth: 08123456789)"
                                           class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden">
                                    <button type="button" 
                                            @click="removeContact(idx)" 
                                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <div x-show="form.contacts.length === 0" class="text-xs text-gray-400 italic">
                                Belum ada kontak ditambahkan. Klik 'Tambah Kontak'.
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Item Barang Dinamis & Auto Kalkulasi -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">
                                3. Item Barang & Perhitungan Otomatis
                            </h4>
                            <button type="button" 
                                    @click="addItem()" 
                                    class="text-xs font-semibold text-[#E62727] hover:underline flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span>Tambah Item</span>
                            </button>
                        </div>

                        <!-- Items Table/List -->
                        <div class="space-y-3">
                            <template x-for="(item, idx) in form.items" :key="idx">
                                <div class="bg-gray-50 p-3.5 rounded-xl border border-gray-200 space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                        <!-- Nama Barang -->
                                        <div class="sm:col-span-4">
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nama Barang</label>
                                            <input type="text" 
                                                   x-model="item.nama_barang" 
                                                   required 
                                                   placeholder="Nama item barang"
                                                   class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-1 focus:ring-[#E62727] outline-hidden bg-white">
                                        </div>

                                        <!-- Qty -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Qty</label>
                                            <input type="number" 
                                                   min="0" 
                                                   step="any"
                                                   x-model.number="item.qty" 
                                                   @input="recalculateItem(item)"
                                                   required 
                                                   class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-1 focus:ring-[#E62727] outline-hidden bg-white">
                                        </div>

                                        <!-- Satuan -->
                                        <div class="sm:col-span-2">
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Satuan</label>
                                            <input type="text" 
                                                   x-model="item.satuan" 
                                                   placeholder="PCS / UNIT"
                                                   class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-1 focus:ring-[#E62727] outline-hidden bg-white uppercase">
                                        </div>

                                        <!-- Harga Satuan -->
                                        <div class="sm:col-span-3">
                                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Harga Satuan</label>
                                            <input type="text" 
                                                   :value="formatRupiahInput(item.harga)"
                                                   @input="onItemPriceChange(item, $event.target.value)"
                                                   placeholder="Rp 0"
                                                   class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-1 focus:ring-[#E62727] outline-hidden bg-white">
                                        </div>

                                        <!-- Delete Item Button -->
                                        <div class="sm:col-span-1 flex justify-end">
                                            <button type="button" 
                                                    @click="removeItem(idx)" 
                                                    class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-100 rounded-lg">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Auto Calculation Summary for this Item -->
                                    <div class="flex flex-wrap items-center justify-between text-xs pt-2 border-t border-gray-200 text-gray-600">
                                        <div>
                                            Jumlah: <span class="font-semibold text-gray-800" x-text="formatRupiah(item.jumlah)"></span>
                                        </div>
                                        <div>
                                            PPN 11%: <span class="font-semibold text-gray-800" x-text="formatRupiah(item.ppn)"></span>
                                        </div>
                                        <div>
                                            Total: <span class="font-bold text-[#E62727]" x-text="formatRupiah(item.total)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="form.items.length === 0" class="text-xs text-gray-400 italic">
                                Belum ada item barang. Klik 'Tambah Item'.
                            </div>
                        </div>

                        <!-- Grand Total Summary Card -->
                        <div class="p-4 bg-gray-100 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
                            <span class="font-semibold text-gray-700">Ringkasan Total Seluruh Item:</span>
                            <div class="flex items-center gap-4 text-xs sm:text-sm">
                                <div>Subtotal: <strong x-text="formatRupiah(grandSubtotal)"></strong></div>
                                <div>PPN: <strong x-text="formatRupiah(grandPpn)"></strong></div>
                                <div class="text-[#E62727] text-base font-extrabold">Total: <span x-text="formatRupiah(grandTotal)"></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Keterangan -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Keterangan / Catatan Tambahan</label>
                        <textarea x-model="form.keterangan" 
                                  rows="2" 
                                  placeholder="Catatan tambahan untuk pesanan ini..."
                                  class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 focus:border-[#E62727] focus:ring-2 focus:ring-[#E62727]/20 outline-hidden"></textarea>
                    </div>

                    <!-- Modal Action Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                @click="closeModal()" 
                                class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="saving"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-[#E62727] hover:bg-[#d01f1f] shadow-md transition disabled:opacity-50 flex items-center gap-2">
                            <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Buat Purchase Order'"></span>
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
    Alpine.data('adminApp', (initialPos) => ({
        pos: initialPos || [],
        searchQuery: '',
        sortCol: 'id',
        sortAsc: false,
        modalOpen: false,
        isEdit: false,
        saving: false,

        form: {
            id: null,
            po: '',
            kostumer: '',
            alamat: '',
            alamat_lengkap: '',
            do: '',
            tgl_po: '',
            tgl_terima_po: '',
            tgl_terbit_do: '',
            deleverydate: '',
            jedahari: null,
            keterangan: '',
            contacts: [],
            items: []
        },

        get filteredPos() {
            let list = [...this.pos];
            if (this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase();
                list = list.filter(p => {
                    const poMatch = (p.po || '').toLowerCase().includes(q);
                    const custMatch = (p.kostumer || '').toLowerCase().includes(q);
                    const alamatMatch = (p.alamat || '').toLowerCase().includes(q);
                    const expMatch = (p.ekspedisi || '').toLowerCase().includes(q);
                    const statusMatch = (p.status || '').toLowerCase().includes(q);
                    const ketMatch = (p.keterangan || '').toLowerCase().includes(q);
                    const itemMatch = (p.items || []).some(it => (it.nama_barang || '').toLowerCase().includes(q));
                    return poMatch || custMatch || alamatMatch || expMatch || statusMatch || ketMatch || itemMatch;
                });
            }

            // Sorting
            list.sort((a, b) => {
                let valA = a[this.sortCol];
                let valB = b[this.sortCol];

                if (valA === null || valA === undefined) valA = '';
                if (valB === null || valB === undefined) valB = '';

                if (typeof valA === 'number' && typeof valB === 'number') {
                    return this.sortAsc ? valA - valB : valB - valA;
                }

                valA = String(valA).toLowerCase();
                valB = String(valB).toLowerCase();

                if (valA < valB) return this.sortAsc ? -1 : 1;
                if (valA > valB) return this.sortAsc ? 1 : -1;
                return 0;
            });

            return list;
        },

        sortBy(col) {
            if (this.sortCol === col) {
                this.sortAsc = !this.sortAsc;
            } else {
                this.sortCol = col;
                this.sortAsc = true;
            }
        },

        sortIcon(col) {
            if (this.sortCol !== col) return '↕';
            return this.sortAsc ? '▲' : '▼';
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            if (isNaN(d)) return dateStr;
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        getStatusBadgeClass(status) {
            switch (status) {
                case 'GAJADI': return 'bg-red-100 text-red-700 border-red-200';
                case 'PENDING': return 'bg-amber-100 text-amber-700 border-amber-200';
                case 'PROSES': return 'bg-blue-100 text-blue-700 border-blue-200';
                case 'SELESAI': return 'bg-emerald-100 text-emerald-700 border-emerald-200';
                case 'DIKIRIM': return 'bg-purple-100 text-purple-700 border-purple-200';
                case 'PERSIAPAN_BARANG': return 'bg-orange-100 text-orange-700 border-orange-200';
                default: return 'bg-gray-100 text-gray-700 border-gray-200';
            }
        },

        openCreateModal() {
            this.isEdit = false;
            this.form = {
                id: null,
                po: '',
                kostumer: '',
                alamat: '',
                alamat_lengkap: '',
                do: '',
                tgl_po: new Date().toISOString().split('T')[0],
                tgl_terima_po: '',
                tgl_terbit_do: '',
                deleverydate: '',
                jedahari: null,
                keterangan: '',
                contacts: [{ contact: '', no_contact: '' }],
                items: [{ nama_barang: '', qty: 1, satuan: 'PCS', harga: 0, jumlah: 0, ppn: 0, total: 0, ready: false }]
            };
            this.modalOpen = true;
        },

        async openEditModal(po) {
            this.isEdit = true;
            try {
                // Fetch latest details
                const res = await fetch(`/po/${po.id}`);
                const data = await res.json();
                this.form = {
                    id: data.id,
                    po: data.po || '',
                    kostumer: data.kostumer || '',
                    alamat: data.alamat || '',
                    alamat_lengkap: data.alamat_lengkap || '',
                    do: data.do || '',
                    tgl_po: data.tgl_po || '',
                    tgl_terima_po: data.tgl_terima_po || '',
                    tgl_terbit_do: data.tgl_terbit_do || '',
                    deleverydate: data.deleverydate || '',
                    jedahari: data.jedahari,
                    keterangan: data.keterangan || '',
                    status: data.status || '-',
                    contacts: data.contacts && data.contacts.length ? JSON.parse(JSON.stringify(data.contacts)) : [{ contact: '', no_contact: '' }],
                    items: data.items && data.items.length ? JSON.parse(JSON.stringify(data.items)) : []
                };
                this.modalOpen = true;
            } catch (err) {
                showToast('Gagal memuat detail PO', 'error');
            }
        },

        closeModal() {
            this.modalOpen = false;
        },

        recalcJedaHari() {
            if (this.form.tgl_terima_po && this.form.deleverydate) {
                const start = new Date(this.form.tgl_terima_po);
                const end = new Date(this.form.deleverydate);
                const diffTime = end - start;
                this.form.jedahari = Math.round(diffTime / (1000 * 60 * 60 * 24));
            } else {
                this.form.jedahari = null;
            }
        },

        addContact() {
            this.form.contacts.push({ contact: '', no_contact: '' });
        },

        removeContact(idx) {
            this.form.contacts.splice(idx, 1);
        },

        addItem() {
            this.form.items.push({
                nama_barang: '',
                qty: 1,
                satuan: 'PCS',
                harga: 0,
                jumlah: 0,
                ppn: 0,
                total: 0,
                ready: false
            });
        },

        removeItem(idx) {
            this.form.items.splice(idx, 1);
        },

        formatRupiahInput(val) {
            if (!val) return 'Rp 0';
            return 'Rp ' + Number(val).toLocaleString('id-ID');
        },

        onItemPriceChange(item, rawVal) {
            const num = parseRupiah(rawVal);
            item.harga = num;
            this.recalculateItem(item);
        },

        recalculateItem(item) {
            const qty = Number(item.qty) || 0;
            const harga = Number(item.harga) || 0;
            item.jumlah = qty * harga;
            item.ppn = Math.round(item.jumlah * 0.11);
            item.total = item.jumlah + item.ppn;
        },

        get grandSubtotal() {
            return (this.form.items || []).reduce((acc, it) => acc + (Number(it.jumlah) || 0), 0);
        },

        get grandPpn() {
            return (this.form.items || []).reduce((acc, it) => acc + (Number(it.ppn) || 0), 0);
        },

        get grandTotal() {
            return (this.form.items || []).reduce((acc, it) => acc + (Number(it.total) || 0), 0);
        },

        async savePo() {
            this.saving = true;
            try {
                const url = this.isEdit ? `/po/${this.form.id}` : '/po';
                const method = this.isEdit ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan saat menyimpan.');
                }

                showToast(data.message || 'PO berhasil disimpan!', 'success');
                this.closeModal();

                // Refresh table data
                const refreshRes = await fetch('/po');
                this.pos = await refreshRes.json();
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                this.saving = false;
            }
        },

        async deletePo(id, poNumber) {
            if (!confirm(`Apakah Anda yakin ingin menghapus Purchase Order ${poNumber}?`)) {
                return;
            }

            try {
                const res = await fetch(`/po/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menghapus');

                showToast(data.message || 'PO berhasil dihapus', 'success');
                this.pos = this.pos.filter(p => p.id !== id);
            } catch (err) {
                showToast(err.message, 'error');
            }
        }
    }));
});
</script>
@endpush
