<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Batok;

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
                'id_user' => auth()->user()->id,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'barang_masuk' => $request->barang_masuk,
                'barang_keluar' => $request->barang_keluar,
                'stok_awal' => $request->stok_awal,
                'stok_akhir' => $request->stok_akhir,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'data' => [
                    'id' => $batok->id,
                    'id_user' => $batok->id_user,
                    'tanggal' => $batok->tanggal,
                    'sumber_batok' => $batok->sumber_batok,
                    'barang_masuk' => $batok->barang_masuk,
                    'barang_keluar' => $batok->barang_keluar,
                    'stok_awal' => $batok->stok_awal,
                    'stok_akhir' => $batok->stok_akhir,
                    'keterangan' => $batok->keterangan,
                    'updated_at' => $batok->updated_at,
                    'created_at' => $batok->created_at,
                ]
            ];

            return response()->json($response, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
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
            $batok = Batok::findOrFail($id);

            $batok->update([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'barang_masuk' => $request->barang_masuk,
                'barang_keluar' => $request->barang_keluar,
                'stok_awal' => $request->stok_awal,
                'stok_akhir' => $request->stok_akhir,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'data' => [
                    'id' => $batok->id,
                    'id_user' => $batok->id_user,
                    'tanggal' => $batok->tanggal,
                    'sumber_batok' => $batok->sumber_batok,
                    'barang_masuk' => $batok->barang_masuk,
                    'barang_keluar' => $batok->barang_keluar,
                    'stok_awal' => $batok->stok_awal,
                    'stok_akhir' => $batok->stok_akhir,
                    'keterangan' => $batok->keterangan,
                    'updated_at' => $batok->updated_at,
                    'created_at' => $batok->created_at,
                ]
            ];

            return response()->json($response, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete($id) {
        DB::beginTransaction();

        try {
            // Temukan data Batok berdasarkan ID
            $batok = Batok::findOrFail($id);

            // Hapus data Batok
            $batok->delete();

            DB::commit();

            return response()->json(['message' => 'Data Batok berhasil dihapus'], 200);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show() {
        DB::beginTransaction();

        try {
            $userId = auth()->user()->id;
            $batok = Batok::where('id_user', $userId)->get();

            DB::commit();

            return response()->json(['data' => $batok], 200);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
