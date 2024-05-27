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
    public function index() {
        try {
            $bahanBaku =  BahanBaku::orderBy('sumber_batok')->orderBy('tanggal', 'desc')->get();
            $groupedBahanBaku = $bahanBaku->groupBy('sumber_batok');
            $response = [];

            foreach ($groupedBahanBaku as $sumber => $listBahanBaku){
                $totalJumlahMasuk = $listBahanBaku->sum('jumlah_masuk');
                $totalJumlahKeluar = $listBahanBaku->sum('jumlah_keluar');

                $persentaseBahanBaku =  $totalJumlahMasuk ? ($totalJumlahMasuk / $totalJumlahKeluar) * 100 : 0;
                $tanggalDitambahkan = $listBahanBaku->first()->tanggal;

                $response[] = [
                    'sumber_batok' => $sumber,
                    'persentase_bahan_baku' => $persentaseBahanBaku,
                    'tanggal' => $tanggalDitambahkan,
                    'list_bahan_baku' => $listBahanBaku,
                ];

             }

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $response], $statusCode);
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'sumber_batok',
            'bahan_baku',
            'jumlah_masuk',
            'jumlah_keluar',
            'keteragan');

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'bahan_baku' => 'required|string',
            'jumlah_masuk' => 'required|numeric',
            'jumlah_keluar' => 'required|numeric',
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
                'keterangan' => $bahanBaku->keterangan,
                'updated_at' => $bahanBaku->updated_at,
                'created_at' => $bahanBaku->created_at,
            ];

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $response], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'bahan_baku' => 'required|string',
            'jumlah_masuk' => 'required|numeric',
            'jumlah_keluar' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            $bahanBaku->update([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'bahan_baku' => $request->bahan_baku,
                'jumlah_masuk' => $request->jumlah_masuk,
                'jumlah_keluar' => $request->jumlah_keluar,
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
                'keterangan' => $bahanBaku->keterangan,
                'updated_at' => $bahanBaku->updated_at,
                'created_at' => $bahanBaku->created_at,
            ];

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $response], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function delete($id) {
        $bahanBaku = BahanBaku::find($id);

        if (!$bahanBaku) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();

        try {
            $bahanBaku->delete();

            DB::commit();

            return response()->json(['message' => 'Data deleted successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $bahanBaku = BahanBaku::find($id);

            if(!$bahanBaku){
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $bahanBaku], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

}
