<?php

namespace Database\Seeders;

use App\Models\Ekspedisi;
use App\Models\Po;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed or update Admin User as requested
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin Gudang',
                'password' => Hash::make('admin123'),
            ]
        );

        if (Ekspedisi::count() === 0) {
            $defaultEkspedisi = [
                ['nama_ekspedisi' => 'JNE', 'link_ekspedisi' => 'https://www.jne.co.id/tracking'],
                ['nama_ekspedisi' => 'J&T Express', 'link_ekspedisi' => 'https://www.jet.co.id/track'],
                ['nama_ekspedisi' => 'SiCepat', 'link_ekspedisi' => 'https://www.sicepat.com/checkAwb'],
                ['nama_ekspedisi' => 'Wahana', 'link_ekspedisi' => 'https://www.wahana.com'],
                ['nama_ekspedisi' => 'Anteraja', 'link_ekspedisi' => 'https://anteraja.id/tracking'],
                ['nama_ekspedisi' => 'Pos Indonesia', 'link_ekspedisi' => 'https://www.posindonesia.co.id/id/tracking'],
                ['nama_ekspedisi' => 'Indah Logistik', 'link_ekspedisi' => 'https://www.indahonline.com/layanan/cek_resi'],
                ['nama_ekspedisi' => 'Dakota Cargo', 'link_ekspedisi' => 'https://dakotacargo.co.id'],
            ];

            foreach ($defaultEkspedisi as $item) {
                Ekspedisi::create($item);
            }
        }

        if (Po::count() === 0) {
            Po::create([
                'po' => 'PO/2026/001',
                'kostumer' => 'PT. Indomulti Maju Mandiri',
                'alamat' => 'Cikarang, Bekasi',
                'alamat_lengkap' => 'Kawasan Industri MM2100 Blok C No. 10, Cikarang Barat',
                'tgl_po' => '2026-03-01',
                'tgl_terima_po' => '2026-03-02',
                'tgl_terbit_do' => '2026-03-05',
                'deleverydate' => '2026-03-06',
                'jedahari' => 4,
                'do' => 'DO/2026/0089',
                'ekspedisi' => 'J&T Express',
                'linkekspedisi' => 'https://www.jet.co.id/track',
                'noresi' => 'JT998231456',
                'no_ekspedisi' => '021-80661888',
                'status' => 'DIKIRIM',
                'keterangan' => 'Pesanan telah siap dan dikirim.',
                'contacts' => [
                    ['contact' => 'Bpk. Hendra Gunawan', 'no_contact' => '081234567890'],
                ],
                'items' => [
                    [
                        'nama_barang' => 'Bearing Skf 6205-2RSH',
                        'qty' => 10,
                        'satuan' => 'PCS',
                        'harga' => 85000,
                        'jumlah' => 850000,
                        'ppn' => 93500,
                        'total' => 943500,
                        'ready' => true,
                    ],
                ],
            ]);
        }
    }
}
