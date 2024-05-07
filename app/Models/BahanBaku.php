<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Batok;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';

    protected $fillable = ['tanggal', 'sumber_batok', 'bahan_baku', 'jumlah_masuk', 'jumlah_keluar', 'stok_awal', 'stok_akhir', 'keterangan'];

    // protected $fillable = ['id_batok', 'tanggal', 'sumber_batok', 'bahan_baku', 'jumlah_masuk', 'jumlah_keluar', 'stok_awal', 'stok_akhir', 'keterangan'];

    // public function batok()
    // {
    //     return $this->belongsTo(Batok::class, 'id_batok');
    // }
}
