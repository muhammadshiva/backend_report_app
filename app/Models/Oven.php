<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oven extends Model
{
    use HasFactory;

    protected $fillable = ['id_batok', 'id_briket', 'tanggal', 'jenis_briket', 'pendinginan_awal', 'pendinginan_akhir', 'pengovenan_masuk', 'pengovenan_keluar', 'keterangan'];

    public function batok()
    {
        return $this->belongsTo(Batok::class, 'id_batok');
    }

    public function briket()
    {
        return $this->belongsTo(Briket::class, 'id_briket');
    }
}
