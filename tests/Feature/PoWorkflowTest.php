<?php

namespace Tests\Feature;

use App\Models\Po;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin Gudang',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);
    }

    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_protected_routes(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/supervisor')->assertRedirect('/login');
        $this->get('/delivery')->assertRedirect('/login');
    }

    public function test_public_tracking_is_accessible_without_auth(): void
    {
        $this->get('/tracking')->assertStatus(200)->assertSee('Tracking Purchase Order');
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $loginRes = $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ]);

        $loginRes->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $loginRes = $this->post('/login', [
            'email' => 'admin@gmail.com',
            'password' => 'salah-password',
        ]);

        $loginRes->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs($this->user);

        $logoutRes = $this->post('/logout');
        $logoutRes->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard_views(): void
    {
        $this->actingAs($this->user);

        $this->get('/admin')->assertStatus(200)->assertSee('Selamat Datang, Admin Gudang');
        $this->get('/supervisor')->assertStatus(200)->assertSee('Selamat Datang, Supervisor Gudang');
        $this->get('/delivery')->assertStatus(200)->assertSee('Selamat Datang, Delivery');
    }

    public function test_can_create_po_with_auto_defaults_and_jedahari_calculation(): void
    {
        $this->actingAs($this->user);

        $payload = [
            'po' => 'PO-TEST-100',
            'kostumer' => 'PT. Test Indonesia',
            'alamat' => 'Jakarta Barat',
            'alamat_lengkap' => 'Jl. Daan Mogot No. 12, Jakarta Barat',
            'tgl_po' => '2026-03-01',
            'tgl_terima_po' => '2026-03-02',
            'tgl_terbit_do' => '2026-03-05',
            'deleverydate' => '2026-03-08', // 6 days difference
            'do' => 'DO-TEST-100',
            'contacts' => [
                ['contact' => 'Budi', 'no_contact' => '08123456789'],
            ],
            'items' => [
                [
                    'nama_barang' => 'Sparepart Gear',
                    'qty' => 5,
                    'satuan' => 'PCS',
                    'harga' => 100000,
                ],
            ],
        ];

        $response = $this->postJson('/po', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('po', [
            'po' => 'PO-TEST-100',
            'kostumer' => 'PT. Test Indonesia',
            'status' => '-',
            'jedahari' => 6,
        ]);

        $createdPo = Po::where('po', 'PO-TEST-100')->first();
        $this->assertNotNull($createdPo);
        $this->assertCount(1, $createdPo->items);
        $item = $createdPo->items[0];
        $this->assertFalse($item['ready']);
        $this->assertEquals(500000, $item['jumlah']);
        $this->assertEquals(55000, $item['ppn']);
        $this->assertEquals(555000, $item['total']);
    }

    public function test_supervisor_can_update_status_and_items(): void
    {
        $this->actingAs($this->user);

        $po = Po::create([
            'po' => 'PO-SPV-01',
            'kostumer' => 'PT. Mitra Sentosa',
            'alamat' => 'Surabaya',
            'alamat_lengkap' => 'Jl. Rungkut Industri No. 5',
            'items' => [
                [
                    'nama_barang' => 'Barang A',
                    'qty' => 2,
                    'satuan' => 'UNIT',
                    'harga' => 50000,
                    'jumlah' => 100000,
                    'ppn' => 11000,
                    'total' => 111000,
                    'ready' => false,
                ],
            ],
            'status' => '-',
        ]);

        $patchData = [
            'status' => 'PERSIAPAN_BARANG',
            'keterangan' => 'Barang sudah diperiksa dan siap di gudang',
            'items' => [
                [
                    'nama_barang' => 'Barang A',
                    'qty' => 2,
                    'satuan' => 'UNIT',
                    'harga' => 50000,
                    'ready' => true,
                ],
            ],
        ];

        $response = $this->patchJson("/po/{$po->id}/status", $patchData);
        $response->assertStatus(200);

        $po->refresh();
        $this->assertEquals('PERSIAPAN_BARANG', $po->status);
        $this->assertEquals('Barang sudah diperiksa dan siap di gudang', $po->keterangan);
        $this->assertTrue($po->items[0]['ready']);
    }

    public function test_supervisor_cannot_uncheck_an_item_that_was_already_marked_ready(): void
    {
        $this->actingAs($this->user);

        $po = Po::create([
            'po' => 'PO-SPV-LOCKED',
            'kostumer' => 'PT. Locked Sentosa',
            'alamat' => 'Surabaya',
            'alamat_lengkap' => 'Jl. Locked No. 5',
            'items' => [
                [
                    'nama_barang' => 'Barang Terkirim',
                    'qty' => 2,
                    'satuan' => 'UNIT',
                    'harga' => 50000,
                    'ready' => true,
                ],
                [
                    'nama_barang' => 'Barang Belum Ready',
                    'qty' => 1,
                    'satuan' => 'UNIT',
                    'harga' => 25000,
                    'ready' => false,
                ],
            ],
            'status' => 'PERSIAPAN_BARANG',
        ]);

        $response = $this->patchJson("/po/{$po->id}/status", [
            'status' => 'PACKING',
            'items' => [
                [
                    'nama_barang' => 'Barang Terkirim',
                    'qty' => 2,
                    'satuan' => 'UNIT',
                    'harga' => 50000,
                    'ready' => false,
                ],
                [
                    'nama_barang' => 'Barang Belum Ready',
                    'qty' => 1,
                    'satuan' => 'UNIT',
                    'harga' => 25000,
                    'ready' => true,
                ],
            ],
        ]);

        $response->assertOk();
        $po->refresh();
        $this->assertTrue($po->items[0]['ready']);
        $this->assertTrue($po->items[1]['ready']);
    }

    public function test_supervisor_sends_newly_ready_items_to_delivery(): void
    {
        $this->actingAs($this->user);

        $po = Po::create([
            'po' => 'PO-SEND-DELIVERY',
            'kostumer' => 'PT. Send Delivery',
            'alamat' => 'Jakarta',
            'alamat_lengkap' => 'Jl. Delivery No. 1',
            'items' => [
                ['nama_barang' => 'Barang A', 'qty' => 100, 'satuan' => 'UNIT', 'ready' => true],
                ['nama_barang' => 'Barang B', 'qty' => 10, 'satuan' => 'PCS', 'ready' => false],
            ],
            'status' => 'PERSIAPAN_BARANG',
        ]);

        $response = $this->postJson("/po/{$po->id}/send-to-delivery", [
            'status' => 'PACKING',
            'items' => $po->items,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('shipments', [
            'po_id' => $po->id,
            'status' => 'MENUNGGU_DELIVERY',
        ]);
        $this->assertDatabaseHas('shipment_items', [
            'nama_barang' => 'Barang A',
            'qty' => 100,
        ]);
        $this->assertDatabaseMissing('shipment_items', [
            'nama_barang' => 'Barang B',
        ]);
    }

    public function test_delivery_can_update_delivery_info_and_recalc_jedahari(): void
    {
        $this->actingAs($this->user);

        $po = Po::create([
            'po' => 'PO-DELIV-01',
            'kostumer' => 'PT. Logistik Jaya',
            'alamat' => 'Bandung',
            'alamat_lengkap' => 'Jl. Soekarno Hatta No. 45',
            'tgl_terima_po' => '2026-03-01',
            'status' => 'PERSIAPAN_BARANG',
        ]);

        // Base64 transparent 1x1 png
        $sampleBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $deliveryData = [
            'status' => 'PENGIRIMAN LENGKAP',
            'ekspedisi' => 'JNE',
            'linkekspedisi' => 'https://jne.co.id/tracking',
            'deleverydate' => '2026-03-05', // 4 days after 2026-03-01
            'noresi' => 'JNE-987654321',
            'no_ekspedisi' => '021-12345678',
            'fotoresi' => $sampleBase64,
        ];

        $response = $this->patchJson("/po/{$po->id}/delivery", $deliveryData);
        $response->assertStatus(200);

        $po->refresh();
        $this->assertEquals('PENGIRIMAN LENGKAP', $po->status);
        $this->assertEquals('JNE', $po->ekspedisi);
        $this->assertEquals(4, $po->jedahari);
        $this->assertNotNull($po->fotoresi);
        $this->assertStringStartsWith('resi-', $po->fotoresi);

        // Fetch detail and verify base64 transformation
        $showRes = $this->getJson("/po/{$po->id}");
        $showRes->assertStatus(200);
        $this->assertStringStartsWith('data:image/', $showRes->json('fotoresi'));
    }

    public function test_tracking_search_finds_po_or_returns_404(): void
    {
        $po = Po::create([
            'po' => 'PO-TRACK-999',
            'kostumer' => 'PT. Pelanggan Setia',
            'alamat' => 'Semarang',
            'alamat_lengkap' => 'Kawasan Candi Semarang',
            'status' => 'DIKIRIM',
        ]);

        $foundRes = $this->postJson('/tracking/search', ['po' => 'PO-TRACK-999']);
        $foundRes->assertStatus(200);
        $this->assertEquals('PT. Pelanggan Setia', $foundRes->json('kostumer'));

        $notFoundRes = $this->postJson('/tracking/search', ['po' => 'PO-TIDAK-ADA']);
        $notFoundRes->assertStatus(404);
    }

    public function test_po_can_have_multiple_shipments_with_different_items(): void
    {
        $this->actingAs($this->user);

        $po = Po::create([
            'po' => 'PO-PARTIAL-01',
            'kostumer' => 'PT. Partial Indonesia',
            'alamat' => 'Jakarta',
            'alamat_lengkap' => 'Jl. Partial No. 1',
            'items' => [
                ['nama_barang' => 'Barang A', 'qty' => 100, 'satuan' => 'UNIT', 'ready' => true],
                ['nama_barang' => 'Barang B', 'qty' => 10, 'satuan' => 'PCS', 'ready' => true],
                ['nama_barang' => 'Barang C', 'qty' => 1, 'satuan' => 'UNIT', 'ready' => true],
            ],
            'status' => 'READY',
        ]);

        $firstShipment = $this->postJson("/po/{$po->id}/shipments", [
            'status' => 'DIKIRIM',
            'ekspedisi' => 'JNE',
            'noresi' => 'JNE-001',
            'items' => [['item_index' => 0]],
        ]);
        $firstShipment->assertCreated();

        $secondShipment = $this->postJson("/po/{$po->id}/shipments", [
            'status' => 'DIKIRIM',
            'ekspedisi' => 'SiCepat',
            'noresi' => 'SICEPAT-002',
            'items' => [['item_index' => 1], ['item_index' => 2]],
        ]);
        $secondShipment->assertCreated();

        $this->assertDatabaseHas('shipments', [
            'po_id' => $po->id,
            'ekspedisi' => 'JNE',
            'noresi' => 'JNE-001',
        ]);
        $this->assertDatabaseHas('shipments', [
            'po_id' => $po->id,
            'ekspedisi' => 'SiCepat',
            'noresi' => 'SICEPAT-002',
        ]);
        $this->assertDatabaseCount('shipment_items', 3);

        $tracking = $this->postJson('/tracking/search', ['po' => 'PO-PARTIAL-01']);
        $tracking->assertOk();
        $this->assertCount(2, $tracking->json('shipments'));
        $this->assertEqualsCanonicalizing(
            ['JNE', 'SiCepat'],
            collect($tracking->json('shipments'))->pluck('ekspedisi')->all()
        );
    }

    public function test_ekspedisi_crud(): void
    {
        $this->actingAs($this->user);

        $createRes = $this->postJson('/ekspedisi', [
            'nama_ekspedisi' => 'Kargo Kilat',
            'link_ekspedisi' => 'https://kargokilat.id/track',
        ]);
        $createRes->assertStatus(201);
        $this->assertDatabaseHas('ekspedisi', ['nama_ekspedisi' => 'Kargo Kilat']);

        $id = $createRes->json('data.id');

        $listRes = $this->getJson('/ekspedisi');
        $listRes->assertStatus(200);

        $deleteRes = $this->deleteJson("/ekspedisi/{$id}");
        $deleteRes->assertStatus(200);
        $this->assertDatabaseMissing('ekspedisi', ['id' => $id]);
    }

    public function test_excel_export_all_and_do(): void
    {
        $this->actingAs($this->user);

        $po = Po::create([
            'po' => 'PO-EXCEL-01',
            'kostumer' => 'PT. Excel Test',
            'alamat' => 'Bekasi',
            'alamat_lengkap' => 'Jl. Boulevard Bekasi Barat',
            'tgl_po' => '2026-03-01',
            'tgl_terima_po' => '2026-03-02',
            'tgl_terbit_do' => '2026-03-05',
            'deleverydate' => '2026-03-06',
            'jedahari' => 4,
            'do' => 'DO-EXCEL-01',
            'items' => [
                [
                    'nama_barang' => 'Item Excel Ready',
                    'qty' => 10,
                    'satuan' => 'PCS',
                    'harga' => 50000,
                    'jumlah' => 500000,
                    'ppn' => 55000,
                    'total' => 555000,
                    'ready' => true,
                ],
            ],
            'status' => 'SELESAI',
        ]);

        // Test export all PO
        $allExcelRes = $this->get('/po/excel/all');
        $allExcelRes->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $allExcelRes->headers->get('Content-Type'));

        // Test export DO Excel with slashes in DO/PO number
        $poWithSlashes = Po::create([
            'po' => 'PO/2026/001/A',
            'kostumer' => 'PT. Slashes Test',
            'alamat' => 'Jakarta',
            'do' => 'DO/2026/05/X',
            'items' => [
                [
                    'nama_barang' => 'Item Slashes',
                    'qty' => 1,
                    'satuan' => 'PCS',
                    'harga' => 10000,
                    'jumlah' => 10000,
                    'ppn' => 1100,
                    'total' => 11100,
                    'ready' => true,
                ],
            ],
            'status' => 'SELESAI',
        ]);

        $doExcelWithSlashesRes = $this->get("/po/{$poWithSlashes->id}/excel");
        $doExcelWithSlashesRes->assertStatus(200);
        $this->assertStringContainsString('spreadsheetml.sheet', $doExcelWithSlashesRes->headers->get('Content-Type'));
        $this->assertStringContainsString('DO-DO-2026-05-X.xlsx', $doExcelWithSlashesRes->headers->get('Content-Disposition'));
    }
}
