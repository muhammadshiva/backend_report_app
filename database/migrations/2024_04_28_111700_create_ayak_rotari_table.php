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
        Schema::create('ayak_rotari', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('id_batok')->constrained('batok')->onDelete('cascade');
            $table->string('sumber_batok');
            $table->double('batok_masuk');
            $table->double('batok_kotor');
            $table->double('hasil_batok');
            $table->double('hasil_abu');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayak_rotari');
    }
};
