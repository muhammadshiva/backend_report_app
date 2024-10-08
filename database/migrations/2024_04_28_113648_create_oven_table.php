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
        Schema::create('oven', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('id_batok')->constrained('batok')->onDelete('cascade');
            // $table->foreignId('id_briket')->constrained('briket')->onDelete('cascade');
            // $table->double('pendinginan');
            // $table->string('sumber_batok');
            $table->string('jenis_masukan');
            $table->date('tanggal');
            $table->string('jenis_briket');
            $table->double('pengovenan');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**s
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oven');
    }
};
