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
        if (! Schema::hasTable('ekspedisi')) {
            Schema::create('ekspedisi', function (Blueprint $table) {
                $table->id();
                $table->string('nama_ekspedisi');
                $table->text('link_ekspedisi')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekspedisi');
    }
};
