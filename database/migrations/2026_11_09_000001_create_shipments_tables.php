<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('po')->cascadeOnDelete();
            $table->string('ekspedisi')->nullable();
            $table->text('linkekspedisi')->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->string('noresi')->nullable();
            $table->string('no_ekspedisi')->nullable();
            $table->text('fotoresi')->nullable();
            $table->string('status')->default('DIKIRIM');
            $table->timestamps();
            $table->index(['po_id', 'status']);
        });

        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->unsignedInteger('item_index');
            $table->string('nama_barang');
            $table->decimal('qty', 15, 3);
            $table->string('satuan')->nullable();
            $table->timestamps();
            $table->unique(['shipment_id', 'item_index']);
        });

        DB::table('po')->orderBy('id')->get()->each(function (object $po): void {
            if (blank($po->ekspedisi) && blank($po->noresi) && blank($po->fotoresi)) {
                return;
            }

            $shipmentId = DB::table('shipments')->insertGetId([
                'po_id' => $po->id,
                'ekspedisi' => $po->ekspedisi,
                'linkekspedisi' => $po->linkekspedisi,
                'tanggal_kirim' => $po->deleverydate,
                'noresi' => $po->noresi,
                'no_ekspedisi' => $po->no_ekspedisi,
                'fotoresi' => $po->fotoresi,
                'status' => $po->status ?: 'DIKIRIM',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach (json_decode($po->items ?: '[]', true) ?: [] as $index => $item) {
                if (! empty($item['ready'])) {
                    DB::table('shipment_items')->insert([
                        'shipment_id' => $shipmentId,
                        'item_index' => $index,
                        'nama_barang' => $item['nama_barang'] ?? 'Barang',
                        'qty' => $item['qty'] ?? 0,
                        'satuan' => $item['satuan'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
