<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('po')) {
            Schema::create('po', function (Blueprint $table) {
                $table->id();
                $table->string('po')->unique();
                $table->date('tgl_po')->nullable();
                $table->date('tgl_terima_po')->nullable();
                $table->date('tgl_terbit_do')->nullable();
                $table->date('deleverydate')->nullable();
                $table->integer('jedahari')->nullable();
                $table->string('kostumer');
                $table->text('alamat');
                $table->text('alamat_lengkap')->nullable();
                $table->json('contacts')->nullable();
                $table->json('items')->nullable();
                $table->string('ekspedisi')->nullable();
                $table->text('linkekspedisi')->nullable();
                $table->text('fotoresi')->nullable();
                $table->text('foto_barang')->nullable();
                $table->string('do')->nullable();
                $table->string('noresi')->nullable();
                $table->string('no_ekspedisi')->nullable();
                $table->string('status')->default('-');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po');
    }
};
