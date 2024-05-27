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
        Schema::create('ayak_manual', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('id_batok')->constrained('batok')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('sumber_batok');
            $table->double('jumlah_batok');
            $table->double('jumlah_batok_mentah');
            $table->double('jumlah_granul');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayak_manuals');
    }
};
