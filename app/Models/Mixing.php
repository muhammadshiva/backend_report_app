<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mixing extends Model
{
    use HasFactory;

    protected $table = 'mixing';

    protected $fillable = ['tanggal','sumber_batok','ukuran_pisau', 'jumlah_arang', 'jumlah_aci', 'jumlah_cairan', 'keterangan'];

}
