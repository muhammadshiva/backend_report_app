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
        Schema::create('batok', function (Blueprint $table) {
            // $table->foreignId('id_user')->constrained('users');
            $table->id();
            $table->string('jenis_masukan');
            $table->date('tanggal');
            $table->string('sumber_batok');
            $table->double('jumlah_batok');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batok');
    }
};
