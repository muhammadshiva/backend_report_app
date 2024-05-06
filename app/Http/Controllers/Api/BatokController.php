<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BatokController extends Controller
{
    public function store(Request $request){

        $data = $request->only('tanggal', 'sumber_batok', 'barang_masuk', 'barang_keluar', 'stok_awal', 'stok_akhir', 'keterangan');

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'barang_masuk' => 'required|numeric',
            'barang_keluar' => 'required|numeric',
            'stok_awal' => 'required|numeric',
            'stok_akhir' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $batok = Batok::create([
                'user_id' => auth()->user()->id,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'barang_masuk' => $request->barang_masuk,
                'barang_keluar' => $request->barang_keluar,
                'stok_awal' => $request->stok_awal,
                'stok_akhir' => $request->stok_akhir,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);

        }


    }
}
