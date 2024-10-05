<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mixing extends Model
{
    use HasFactory;

    protected $table = 'mixing';

    protected $fillable = ['tanggal', 'ukuran_pisau', 'jumlah_aci', 'jumlah_cairan', 'jumlah_arang_sulawesi', 'jumlah_arang_sumatera', 'jumlah_arang_kayu', 'keterangan'];
}
