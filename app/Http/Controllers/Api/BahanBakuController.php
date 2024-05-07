<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\BahanBaku;

class BahanBakuController extends Controller
{
    public function store(Request $request){
        $data = $request->only('tanggal', 'sumber_batok', 'bahan_baku', 'jumlah_masuk', 'jumlah_keluar', 'stok_awal', 'stok_akhir', 'keteragan');

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'bahan_baku' => 'required|string',
            'jumlah_masuk' => 'required|numeric',
            'jumlah_keluar' => 'required|numeric',
            'stok_awal' => 'required|numeric',
            'stok_akhir' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $bahanBaku = BahanBaku::create([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'bahan_baku' => $request->bahan_baku,
                'jumlah_masuk' => $request->jumlah_masuk,
                'jumlah_keluar' => $request->jumlah_keluar,
                'stok_awal' => $request->stok_awal,
                'stok_akhir' => $request->stok_akhir,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'id' => $bahanBaku->id,
                'tanggal' => $bahanBaku->tanggal,
                'sumber_batok' => $bahanBaku->sumber_batok,
                'bahan_baku' => $bahanBaku->bahan_baku,
                'jumlah_masuk' => $bahanBaku->jumlah_masuk,
                'jumlah_keluar' => $bahanBaku->jumlah_keluar,
                'stok_awal' => $bahanBaku->stok_awal,
                'stok_akhir' => $bahanBaku->stok_akhir,
                'keterangan' => $bahanBaku->keterangan,
                'updated_at' => $bahanBaku->updated_at,
                'created_at' => $bahanBaku->created_at,
            ];

            // Membuat respons JSON dengan data yang diformat
            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
