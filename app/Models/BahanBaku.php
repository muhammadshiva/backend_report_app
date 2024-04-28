<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $fillable = ['id_batok', 'tanggal', 'sumber_batok', 'bahan_baku', 'jumlah_masuk', 'jumlah_keluar', 'stok_awal', 'stok_akhir', 'keterangan'];

    public function batok()
    {
        return $this->belongsTo(Batok::class, 'id_batok');
    }
}
