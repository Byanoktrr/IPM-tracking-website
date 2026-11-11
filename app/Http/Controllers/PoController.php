<?php

namespace App\Http\Controllers;

use App\Models\Ekspedisi;
use App\Models\Po;
use App\Models\Shipment;
use App\Services\ExcelExportService;
use App\Services\ImageStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PoController extends Controller
{
    public function __construct(
        public ImageStorageService $imageStorageService,
        public ExcelExportService $excelExportService,
    ) {}

    /**
     * Admin Gudang View.
     */
    public function adminView(): View
    {
        $pos = Po::orderBy('id', 'desc')->get();

        return view('pages.admin', compact('pos'));
    }

    /**
     * Supervisor Gudang View.
     */
    public function supervisorView(): View
    {
        $pos = Po::orderBy('id', 'desc')->get();

        return view('pages.supervisor', compact('pos'));
    }

    /**
     * Delivery View.
     */
    public function deliveryView(): View
    {
        $pos = Po::with('shipments.items')->orderBy('id', 'desc')->get();
        $ekspedisis = Ekspedisi::orderBy('nama_ekspedisi', 'asc')->get();

        return view('pages.delivery', compact('pos', 'ekspedisis'));
    }

    /**
     * Public Tracking View.
     */
    public function trackingView(Request $request): View
    {
        $queryPo = $request->query('po');
        $poData = null;

        if ($queryPo) {
            $po = Po::where('po', trim((string) $queryPo))->first();
            if ($po) {
                $poData = $this->transformPoForClient($po);
            }
        }

        return view('pages.tracking', [
            'initialPoQuery' => $queryPo,
            'initialPoData' => $poData,
        ]);
    }

    /**
     * Get all PO list as JSON.
     */
    public function index(): JsonResponse
    {
        $pos = Po::with('shipments.items')->orderBy('id', 'desc')->get();

        return response()->json($pos);
    }

    /**
     * Get a single PO with images converted to base64 Data URI if found on disk.
     */
    public function show(int $id): JsonResponse
    {
        $po = Po::findOrFail($id);

        return response()->json($this->transformPoForClient($po));
    }

    /**
     * Search PO by number for public tracking.
     */
    public function searchTracking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'po' => ['required', 'string'],
        ]);

        $po = Po::where('po', trim($validated['po']))->first();

        if (! $po) {
            return response()->json([
                'message' => 'Purchase Order tidak ditemukan',
            ], 404);
        }

        return response()->json($this->transformPoForClient($po));
    }

    /**
     * Store a newly created PO.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'po' => ['required', 'string', 'max:255', 'unique:po,po'],
            'kostumer' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'alamat_lengkap' => ['required', 'string'],
            'tgl_po' => ['nullable', 'date'],
            'tgl_terima_po' => ['nullable', 'date'],
            'tgl_terbit_do' => ['nullable', 'date'],
            'deleverydate' => ['nullable', 'date'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.contact' => ['nullable', 'string'],
            'contacts.*.no_contact' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'ekspedisi' => ['nullable', 'string', 'max:255'],
            'linkekspedisi' => ['nullable', 'string'],
            'fotoresi' => ['nullable', 'string'],
            'foto_barang' => ['nullable', 'string'],
            'do' => ['nullable', 'string', 'max:255'],
            'noresi' => ['nullable', 'string', 'max:255'],
            'no_ekspedisi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        // Auto default ready: false for all items
        $formattedItems = [];
        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                $harga = (float) ($item['harga'] ?? 0);
                $jumlah = $qty * $harga;
                $ppn = round($jumlah * 0.11);
                $total = $jumlah + $ppn;

                $formattedItems[] = [
                    'nama_barang' => $item['nama_barang'] ?? '',
                    'brand' => $item['brand'] ?? '',
                    'qty' => $qty,
                    'satuan' => $item['satuan'] ?? '',
                    'harga' => $harga,
                    'jumlah' => $jumlah,
                    'ppn' => $ppn,
                    'total' => $total,
                    'ready' => false,
                ];
            }
        }

        // Calculate jedahari
        $jedahari = Po::calculateJedaHari(
            $validated['tgl_terima_po'] ?? null,
            $validated['deleverydate'] ?? null
        );

        $po = new Po;
        $po->po = $validated['po'];
        $po->kostumer = $validated['kostumer'];
        $po->alamat = $validated['alamat'];
        $po->alamat_lengkap = $validated['alamat_lengkap'];
        $po->tgl_po = $validated['tgl_po'] ?? null;
        $po->tgl_terima_po = $validated['tgl_terima_po'] ?? null;
        $po->tgl_terbit_do = $validated['tgl_terbit_do'] ?? null;
        $po->deleverydate = $validated['deleverydate'] ?? null;
        $po->jedahari = $jedahari;
        $po->contacts = $validated['contacts'] ?? [];
        $po->items = $formattedItems;
        $po->ekspedisi = $validated['ekspedisi'] ?? null;
        $po->linkekspedisi = $validated['linkekspedisi'] ?? null;
        $po->do = $validated['do'] ?? null;
        $po->noresi = $validated['noresi'] ?? null;
        $po->no_ekspedisi = $validated['no_ekspedisi'] ?? null;
        $po->status = '-'; // Initial default status as per spec
        $po->keterangan = $validated['keterangan'] ?? null;
        $po->save();

        // Process image uploads if provided in request
        if (! empty($validated['fotoresi'])) {
            $po->fotoresi = $this->imageStorageService->saveImage($validated['fotoresi'], $po->id, 'resi');
        }
        if (! empty($validated['foto_barang'])) {
            $po->foto_barang = $this->imageStorageService->saveImage($validated['foto_barang'], $po->id, 'barang');
        }
        $po->save();

        return response()->json([
            'message' => 'Purchase Order berhasil dibuat',
            'data' => $this->transformPoForClient($po),
        ], 201);
    }

    /**
     * Update the specified PO (Admin Edit).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $po = Po::findOrFail($id);

        $validated = $request->validate([
            'po' => ['required', 'string', 'max:255', "unique:po,po,{$id}"],
            'kostumer' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'alamat_lengkap' => ['required', 'string'],
            'tgl_po' => ['nullable', 'date'],
            'tgl_terima_po' => ['nullable', 'date'],
            'tgl_terbit_do' => ['nullable', 'date'],
            'deleverydate' => ['nullable', 'date'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.contact' => ['nullable', 'string'],
            'contacts.*.no_contact' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'ekspedisi' => ['nullable', 'string', 'max:255'],
            'linkekspedisi' => ['nullable', 'string'],
            'fotoresi' => ['nullable', 'string'],
            'foto_barang' => ['nullable', 'string'],
            'do' => ['nullable', 'string', 'max:255'],
            'noresi' => ['nullable', 'string', 'max:255'],
            'no_ekspedisi' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        // Process items
        $formattedItems = [];
        if (! empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $qty = (float) ($item['qty'] ?? 0);
                $harga = (float) ($item['harga'] ?? 0);
                $jumlah = $qty * $harga;
                $ppn = round($jumlah * 0.11);
                $total = $jumlah + $ppn;

                $formattedItems[] = [
                    'nama_barang' => $item['nama_barang'] ?? '',
                    'brand' => $item['brand'] ?? '',
                    'qty' => $qty,
                    'satuan' => $item['satuan'] ?? '',
                    'harga' => $harga,
                    'jumlah' => $jumlah,
                    'ppn' => $ppn,
                    'total' => $total,
                    'ready' => ! empty($item['ready']),
                ];
            }
        }

        // Recalculate jedahari
        $jedahari = Po::calculateJedaHari(
            $validated['tgl_terima_po'] ?? null,
            $validated['deleverydate'] ?? null
        );

        $po->po = $validated['po'];
        $po->kostumer = $validated['kostumer'];
        $po->alamat = $validated['alamat'];
        $po->alamat_lengkap = $validated['alamat_lengkap'];
        $po->tgl_po = $validated['tgl_po'] ?? null;
        $po->tgl_terima_po = $validated['tgl_terima_po'] ?? null;
        $po->tgl_terbit_do = $validated['tgl_terbit_do'] ?? null;
        $po->deleverydate = $validated['deleverydate'] ?? null;
        $po->jedahari = $jedahari;
        $po->contacts = $validated['contacts'] ?? [];
        $po->items = $formattedItems;
        $po->ekspedisi = $validated['ekspedisi'] ?? null;
        $po->linkekspedisi = $validated['linkekspedisi'] ?? null;
        $po->do = $validated['do'] ?? null;
        $po->noresi = $validated['noresi'] ?? null;
        $po->no_ekspedisi = $validated['no_ekspedisi'] ?? null;
        if (isset($validated['status'])) {
            $po->status = $validated['status'];
        }
        $po->keterangan = $validated['keterangan'] ?? null;

        // Process images if provided
        if (! empty($validated['fotoresi'])) {
            $po->fotoresi = $this->imageStorageService->saveImage($validated['fotoresi'], $po->id, 'resi');
        }
        if (! empty($validated['foto_barang'])) {
            $po->foto_barang = $this->imageStorageService->saveImage($validated['foto_barang'], $po->id, 'barang');
        }

        $po->save();
        $this->syncLegacyDeliveryToShipment($po);

        return response()->json([
            'message' => 'Purchase Order berhasil diperbarui',
            'data' => $this->transformPoForClient($po),
        ]);
    }

    /**
     * Update PO status & items checklist (Supervisor Edit).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $po = Po::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
        ]);

        $this->saveSupervisorStatus($po, $validated);

        return response()->json([
            'message' => 'Status dan items Purchase Order berhasil diperbarui',
            'data' => $this->transformPoForClient($po),
        ]);
    }

    /**
     * Send all newly-ready items to Delivery as one pending shipment.
     */
    public function sendToDelivery(Request $request, int $id): JsonResponse
    {
        $po = Po::findOrFail($id);
        $validated = $request->validate([
            'status' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
        ]);

        $this->saveSupervisorStatus($po, $validated);

        $assignedItemIndexes = $po->shipments()->with('items')->get()
            ->flatMap(fn (Shipment $shipment) => $shipment->items->pluck('item_index'))
            ->map(fn (int $index): int => $index)
            ->unique();
        $readyItems = collect($po->items ?? [])->filter(
            fn (array $item, int $index): bool => ! empty($item['ready']) && ! $assignedItemIndexes->contains($index)
        );

        if ($readyItems->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada barang ready baru untuk dikirim ke Delivery.',
            ], 422);
        }

        $shipment = $po->shipments()->create([
            'status' => 'MENUNGGU_DELIVERY',
        ]);
        $shipment->items()->createMany($readyItems->map(
            fn (array $item, int $index): array => [
                'item_index' => $index,
                'nama_barang' => $item['nama_barang'] ?? 'Barang',
                'qty' => (float) ($item['qty'] ?? 0),
                'satuan' => $item['satuan'] ?? null,
            ]
        )->values()->all());

        return response()->json([
            'message' => 'Barang ready berhasil dikirim ke Delivery.',
            'data' => $this->transformPoForClient($po->fresh()),
        ]);
    }

    /**
     * Update PO delivery information (Delivery Edit).
     */
    public function updateDelivery(Request $request, int $id): JsonResponse
    {
        $po = Po::findOrFail($id);

        $validated = $request->validate([
            'status' => ['nullable', 'string', 'max:255'],
            'ekspedisi' => ['nullable', 'string', 'max:255'],
            'linkekspedisi' => ['nullable', 'string'],
            'deleverydate' => ['nullable', 'date'],
            'noresi' => ['nullable', 'string', 'max:255'],
            'no_ekspedisi' => ['nullable', 'string', 'max:255'],
            'fotoresi' => ['nullable', 'string'],
            'foto_barang' => ['nullable', 'string'],
        ]);

        if (isset($validated['status'])) {
            $po->status = $validated['status'];
        }
        if (isset($validated['ekspedisi'])) {
            $po->ekspedisi = $validated['ekspedisi'];
        }
        if (isset($validated['linkekspedisi'])) {
            $po->linkekspedisi = $validated['linkekspedisi'];
        }
        if (isset($validated['deleverydate'])) {
            $po->deleverydate = $validated['deleverydate'];

            // Recalculate jedahari when deliverydate updates
            $tglTerimaPo = $po->tgl_terima_po ? (is_string($po->tgl_terima_po) ? $po->tgl_terima_po : $po->tgl_terima_po->format('Y-m-d')) : null;
            $po->jedahari = Po::calculateJedaHari($tglTerimaPo, $validated['deleverydate']);
        }
        if (isset($validated['noresi'])) {
            $po->noresi = $validated['noresi'];
        }
        if (isset($validated['no_ekspedisi'])) {
            $po->no_ekspedisi = $validated['no_ekspedisi'];
        }

        // Process images
        if (! empty($validated['fotoresi'])) {
            $po->fotoresi = $this->imageStorageService->saveImage($validated['fotoresi'], $po->id, 'resi');
        }
        if (! empty($validated['foto_barang'])) {
            $po->foto_barang = $this->imageStorageService->saveImage($validated['foto_barang'], $po->id, 'barang');
        }

        $po->save();
        $this->syncLegacyDeliveryToShipment($po);

        return response()->json([
            'message' => 'Informasi pengiriman berhasil diperbarui',
            'data' => $this->transformPoForClient($po),
        ]);
    }

    /**
     * Create one shipment for selected complete PO items.
     */
    public function storeShipment(Request $request, int $id): JsonResponse
    {
        $po = Po::findOrFail($id);
        $validated = $request->validate($this->shipmentRules());

        $itemIndexes = collect($validated['items'])->pluck('item_index')->unique()->values();
        $items = collect($po->items ?? []);
        $selectedItems = $itemIndexes->map(function (int $index) use ($items): array {
            $item = $items->get($index);
            abort_if($item === null, 422, 'Item pengiriman tidak ditemukan.');

            return [
                'item_index' => $index,
                'nama_barang' => $item['nama_barang'] ?? 'Barang',
                'qty' => (float) ($item['qty'] ?? 0),
                'satuan' => $item['satuan'] ?? null,
            ];
        });

        $alreadyShipped = $po->shipments()->with('items')->get()
            ->flatMap(fn (Shipment $shipment) => $shipment->items->pluck('item_index'));
        if ($itemIndexes->intersect($alreadyShipped)->isNotEmpty()) {
            return response()->json([
                'message' => 'Sebagian item sudah masuk ke pengiriman lain. Pilih item yang belum dikirim.',
            ], 422);
        }

        $shipment = DB::transaction(function () use ($po, $validated, $selectedItems): Shipment {
            $shipment = $po->shipments()->create([
                'ekspedisi' => $validated['ekspedisi'] ?? null,
                'linkekspedisi' => $validated['linkekspedisi'] ?? null,
                'tanggal_kirim' => $validated['tanggal_kirim'] ?? null,
                'noresi' => $validated['noresi'] ?? null,
                'no_ekspedisi' => $validated['no_ekspedisi'] ?? null,
                'status' => $validated['status'] ?? 'DIKIRIM',
            ]);

            $shipment->items()->createMany($selectedItems->all());

            if (! empty($validated['fotoresi'])) {
                $shipment->fotoresi = $this->imageStorageService->saveImage(
                    $validated['fotoresi'],
                    $shipment->id,
                    'resi'
                );
                $shipment->save();
            }

            $po->update(['status' => $validated['status'] ?? 'DIKIRIM']);

            return $shipment;
        });

        return response()->json([
            'message' => 'Pengiriman berhasil ditambahkan',
            'data' => $this->transformPoForClient($po->fresh()),
        ], 201);
    }

    /**
     * Update an existing shipment and its selected complete PO items.
     */
    public function updateShipment(Request $request, int $poId, int $shipmentId): JsonResponse
    {
        $po = Po::findOrFail($poId);
        $shipment = $po->shipments()->findOrFail($shipmentId);
        $validated = $request->validate($this->shipmentRules());
        $itemIndexes = collect($validated['items'] ?? [])->pluck('item_index')->unique()->values();
        $items = collect($po->items ?? []);

        $selectedItems = $itemIndexes->map(function (int $index) use ($items): array {
            $item = $items->get($index);
            abort_if($item === null, 422, 'Item pengiriman tidak ditemukan.');

            return [
                'item_index' => $index,
                'nama_barang' => $item['nama_barang'] ?? 'Barang',
                'qty' => (float) ($item['qty'] ?? 0),
                'satuan' => $item['satuan'] ?? null,
            ];
        });

        if ($itemIndexes->isNotEmpty()) {
            $alreadyShipped = $po->shipments()->with('items')->get()
                ->reject(fn (Shipment $other) => $other->id === $shipment->id)
                ->flatMap(fn (Shipment $other) => $other->items->pluck('item_index'));
            if ($itemIndexes->intersect($alreadyShipped)->isNotEmpty()) {
                return response()->json([
                    'message' => 'Sebagian item sudah masuk ke pengiriman lain.',
                ], 422);
            }
        }

        DB::transaction(function () use ($shipment, $validated, $selectedItems): void {
            $shipment->update([
                'ekspedisi' => $validated['ekspedisi'] ?? null,
                'linkekspedisi' => $validated['linkekspedisi'] ?? null,
                'tanggal_kirim' => $validated['tanggal_kirim'] ?? null,
                'noresi' => $validated['noresi'] ?? null,
                'no_ekspedisi' => $validated['no_ekspedisi'] ?? null,
                'status' => $validated['status'] ?? $shipment->status,
            ]);
            if ($selectedItems->isNotEmpty()) {
                $shipment->items()->delete();
                $shipment->items()->createMany($selectedItems->all());
            }

            if (! empty($validated['fotoresi'])) {
                $shipment->fotoresi = $this->imageStorageService->saveImage(
                    $validated['fotoresi'],
                    $shipment->id,
                    'resi'
                );
                $shipment->save();
            }
        });

        return response()->json([
            'message' => 'Pengiriman berhasil diperbarui',
            'data' => $this->transformPoForClient($po->fresh()),
        ]);
    }

    /**
     * Remove the specified PO.
     */
    public function destroy(int $id): JsonResponse
    {
        $po = Po::findOrFail($id);
        $po->delete();

        return response()->json([
            'message' => 'Purchase Order berhasil dihapus',
        ]);
    }

    /**
     * Serve uploaded image from storage.
     */
    public function serveImage(string $filename): BinaryFileResponse|JsonResponse
    {
        $filePath = $this->imageStorageService->getFilePath($filename);

        if (! $filePath || ! File::exists($filePath)) {
            return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
        }

        return response()->file($filePath);
    }

    /**
     * Download Excel Delivery Order (DO) per PO.
     */
    public function exportDeliveryOrder(int $id): StreamedResponse
    {
        $po = Po::findOrFail($id);

        return $this->excelExportService->exportDeliveryOrder($po);
    }

    /**
     * Download Excel of all PO data.
     */
    public function exportAllPo(): StreamedResponse
    {
        return $this->excelExportService->exportAllPo();
    }

    /**
     * Helper to transform PO entity for client response (converts images to base64 data URI).
     */
    private function transformPoForClient(Po $po): array
    {
        $po->loadMissing('shipments.items');
        $data = $po->toArray();

        // Format dates as Y-m-d for date input compatibility
        $data['tgl_po'] = $po->tgl_po ? (is_string($po->tgl_po) ? substr($po->tgl_po, 0, 10) : $po->tgl_po->format('Y-m-d')) : null;
        $data['tgl_terima_po'] = $po->tgl_terima_po ? (is_string($po->tgl_terima_po) ? substr($po->tgl_terima_po, 0, 10) : $po->tgl_terima_po->format('Y-m-d')) : null;
        $data['tgl_terbit_do'] = $po->tgl_terbit_do ? (is_string($po->tgl_terbit_do) ? substr($po->tgl_terbit_do, 0, 10) : $po->tgl_terbit_do->format('Y-m-d')) : null;
        $data['deleverydate'] = $po->deleverydate ? (is_string($po->deleverydate) ? substr($po->deleverydate, 0, 10) : $po->deleverydate->format('Y-m-d')) : null;

        // Transform fotoresi and foto_barang to base64 Data URI if found in storage
        if (! empty($po->fotoresi)) {
            $data['fotoresi'] = $this->imageStorageService->toBase64Uri($po->fotoresi);
        }
        if (! empty($po->foto_barang)) {
            $data['foto_barang'] = $this->imageStorageService->toBase64Uri($po->foto_barang);
        }

        $data['shipments'] = collect($po->shipments)->map(function (Shipment $shipment): array {
            $shipmentData = $shipment->toArray();
            $shipmentData['tanggal_kirim'] = $shipment->tanggal_kirim?->format('Y-m-d');
            $shipmentData['fotoresi'] = $this->imageStorageService->toBase64Uri($shipment->fotoresi);

            return $shipmentData;
        })->values()->all();

        return $data;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function shipmentRules(): array
    {
        return [
            'status' => ['nullable', 'string', 'max:255'],
            'ekspedisi' => ['nullable', 'string', 'max:255'],
            'linkekspedisi' => ['nullable', 'string', 'max:2000'],
            'tanggal_kirim' => ['nullable', 'date'],
            'noresi' => ['nullable', 'string', 'max:255'],
            'no_ekspedisi' => ['nullable', 'string', 'max:255'],
            'fotoresi' => ['nullable', 'string'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.item_index' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Persist supervisor status and preserve an item once it is ready.
     *
     * @param  array{status: string, keterangan?: string|null, items?: array<int, array<string, mixed>>}  $validated
     */
    private function saveSupervisorStatus(Po $po, array $validated): void
    {
        $po->status = $validated['status'];
        $po->keterangan = $validated['keterangan'] ?? null;

        if (isset($validated['items'])) {
            $formattedItems = [];
            foreach ($validated['items'] as $index => $item) {
                $qty = (float) ($item['qty'] ?? 0);
                $harga = (float) ($item['harga'] ?? 0);
                $jumlah = $qty * $harga;
                $ppn = round($jumlah * 0.11);
                $total = $jumlah + $ppn;
                $wasReady = ! empty(($po->items ?? [])[$index]['ready']);

                $formattedItems[] = [
                    'nama_barang' => $item['nama_barang'] ?? '',
                    'brand' => $item['brand'] ?? '',
                    'qty' => $qty,
                    'satuan' => $item['satuan'] ?? '',
                    'harga' => $harga,
                    'jumlah' => $jumlah,
                    'ppn' => $ppn,
                    'total' => $total,
                    'ready' => $wasReady || ! empty($item['ready']),
                ];
            }
            $po->items = $formattedItems;
        }

        $po->save();
    }

    private function syncLegacyDeliveryToShipment(Po $po): void
    {
        if (blank($po->ekspedisi) && blank($po->noresi) && blank($po->fotoresi)) {
            return;
        }

        $shipment = $po->shipments()->firstOrNew();
        $shipment->fill([
            'ekspedisi' => $po->ekspedisi,
            'linkekspedisi' => $po->linkekspedisi,
            'tanggal_kirim' => $po->deleverydate,
            'noresi' => $po->noresi,
            'no_ekspedisi' => $po->no_ekspedisi,
            'fotoresi' => $po->fotoresi,
            'status' => $po->status ?: 'DIKIRIM',
        ]);
        $shipment->save();

        $shipment->items()->delete();
        collect($po->items ?? [])->each(function (array $item, int $index) use ($shipment): void {
            if (empty($item['ready'])) {
                return;
            }

            $shipment->items()->create([
                'item_index' => $index,
                'nama_barang' => $item['nama_barang'] ?? 'Barang',
                'qty' => (float) ($item['qty'] ?? 0),
                'satuan' => $item['satuan'] ?? null,
            ]);
        });
    }
}
