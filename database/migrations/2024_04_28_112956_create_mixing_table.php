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
        Schema::create('mixing', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('id_batok')->constrained('batok')->onDelete('cascade');
            // $table->string('sumber_batok');
            $table->date('tanggal');
            $table->string('ukuran_pisau');
            $table->double('jumlah_aci');
            $table->double('jumlah_cairan');
            $table->double('jumlah_arang_sulawesi');
            $table->double('jumlah_arang_sumatera');
            $table->double('jumlah_arang_kayu');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mixing');
    }
};
