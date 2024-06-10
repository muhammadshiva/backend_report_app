<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Briket extends Model
{
    use HasFactory;

    protected $table = 'briket';

    protected $fillable = ['jenis_masukan','tanggal','sumber_batok','jenis_briket', 'stok', 'keterangan'];

}
