<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskmill extends Model
{
    use HasFactory;

    protected $table = 'diskmill';

    protected $fillable = ['tanggal', 'batok_masuk', 'hasil_pisau_02', 'hasil_pisau_03', 'keterangan'];

    // BACKUP
    // protected $fillable = ['id_batok', 'tanggal', 'batok_masuk', 'hasil_pisau_02', 'hasil_pisau_03', 'keterangan'];

    // public function batok()
    // {
    //     return $this->belongsTo(Batok::class, 'id_batok');
    // }
}
