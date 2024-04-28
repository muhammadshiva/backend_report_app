<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Briket extends Model
{
    use HasFactory;

    protected $fillable = ['id_batok', 'tanggal', 'jenis_briket', 'stok_awal', 'stok_akhir', 'keterangan'];

    public function batok(){
        return $this->belongsTo(Batok::class, 'id_batok');
    }

}
