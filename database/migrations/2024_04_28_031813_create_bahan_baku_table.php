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
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_batok')->constrained('batok')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('sumber_batok');
            $table->string('bahan_baku');
            $table->double('jumlah_masuk');
            $table->double('jumlah_keluar');
            $table->double('stok_awal');
            $table->double('stok_akhir');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_bakus');
    }
};
