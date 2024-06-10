<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oven extends Model
{
    use HasFactory;

    protected $table = 'oven';

    protected $fillable = ['jenis_masukan', 'tanggal', 'sumber_batok', 'jenis_briket', 'pengovenan', 'pendinginan', 'keterangan'];


}
