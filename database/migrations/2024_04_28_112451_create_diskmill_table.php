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
        Schema::create('diskmill', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('id_batok')->constrained('batok')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('sumber_batok');
            $table->double('batok_masuk');
            $table->double('hasil_pisau_02');
            $table->double('hasil_pisau_03');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diskmill');
    }
};
