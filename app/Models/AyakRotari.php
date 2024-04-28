<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyakRotari extends Model
{
    use HasFactory;

    protected $fillable = ['id_batok', 'batok_masuk', 'batok_kotor', 'hasil_batok', 'hasil_abu', 'keterangan'];

    public function batok()
    {
        return $this->belongsTo(Batok::class, 'id_batok');
    }

}
