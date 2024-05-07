<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Batok extends Model
{
    use HasFactory;

    protected $table = 'batok';

    protected $fillable = [
        // 'id_user',
        'tanggal',
        'sumber_batok',
        'barang_masuk',
        'barang_keluar',
        'stok_awal',
        'stok_akhir',
        'keterangan',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }
}
