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
        'jenis_masukan',
        'tanggal',
        'sumber_batok',
        'jumlah_batok',
        'keterangan',
    ];

}
