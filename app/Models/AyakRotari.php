<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyakRotari extends Model
{
    use HasFactory;

    protected $table = 'ayak_rotari';

    protected $fillable = ['tanggal','sumber_batok','batok_masuk', 'batok_kotor', 'hasil_batok', 'hasil_abu', 'keterangan'];

}
