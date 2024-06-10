<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Batok;

class BahanBaku extends Model
{
    use HasFactory;

    protected $table = 'bahan_baku';

    protected $fillable = [
        'jenis_masukan',
        'tanggal',
        'sumber_batok',
        'bahan_baku',
        'jumlah',
        'keterangan'
    ];
}
