<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\AyakManual;

class AyakManualController extends Controller
{
    public function index() {
        try {
            $ayakManuals = AyakManual::all();
            return response()->json(['data' => $ayakManuals], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'jumlah_batok',
            'jumlah_batok_mentah',
            'jumlah_granul',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'jumlah_batok' => 'required|numeric',
            'jumlah_batok_mentah' => 'required|numeric',
            'jumlah_granul' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $ayakManual = AyakManual::create([
                'tanggal' => $request->tanggal,
                'jumlah_batok' => $request->jumlah_batok,
                'jumlah_batok_mentah' => $request->jumlah_batok_mentah,
                'jumlah_granul' => $request->jumlah_granul,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $ayakManual->id,
                'jumlah_batok' => $ayakManual->jumlah_batok,
                'jumlah_batok_mentah' => $ayakManual->jumlah_batok_mentah,
                'jumlah_granul' => $ayakManual->jumlah_granul,
                'keterangan' => $ayakManual->keterangan
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        // Temukan data AyakManual berdasarkan ID
        $ayakManual = AyakManual::find($id);

        // Jika data tidak ditemukan, kembalikan respons dengan status 404 (Not Found)
        if (!$ayakManual) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // Validasi data yang diterima dari permintaan
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'jumlah_batok' => 'required|numeric',
            'jumlah_batok_mentah' => 'required|numeric',
            'jumlah_granul' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        // Jika validasi gagal, kembalikan pesan error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Perbarui data AyakManual
            $ayakManual->update([
                'tanggal' => $request->tanggal,
                'jumlah_batok' => $request->jumlah_batok,
                'jumlah_batok_mentah' => $request->jumlah_batok_mentah,
                'jumlah_granul' => $request->jumlah_granul,
                'keterangan' => $request->keterangan
            ]);

            // Commit transaksi database
            DB::commit();

            // Buat respons JSON dengan data yang diperbarui
            return response()->json(['data' => $ayakManual], 200);
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            // Kembalikan pesan error
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete($id) {
        // Temukan data AyakManual berdasarkan ID
        $ayakManual = AyakManual::find($id);

        // Jika data tidak ditemukan, kembalikan respons dengan status 404 (Not Found)
        if (!$ayakManual) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Hapus data AyakManual
            $ayakManual->delete();

            // Commit transaksi database
            DB::commit();

            // Kembalikan respons sukses dengan status 200 (OK)
            return response()->json(['message' => 'Data deleted successfully'], 200);
        } catch (\Throwable $th) {
            // Rollback transaksi jika terjadi error
            DB::rollback();
            // Kembalikan respons dengan status 500 (Internal Server Error) jika terjadi error
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id) {

        try {
            $ayakManual = AyakManual::find($id);

            if (!$ayakManual) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $ayakManual], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

}
