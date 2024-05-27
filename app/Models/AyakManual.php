<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyakManual extends Model
{
    use HasFactory;

    protected $table = 'ayak_manual';

    protected $fillable = [
        'tanggal',
        'jumlah_batok',
        'jumlah_batok_mentah',
        'jumlah_granul',
        'keterangan'
    ];
}
