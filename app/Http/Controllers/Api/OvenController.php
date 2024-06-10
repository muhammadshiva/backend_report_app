<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Oven;
use Carbon\Carbon;

class OvenController extends Controller
{
    public function index(Request $request){
        try {
            // Dapatkan parameter filter_by dari request
            $filterBy = $request->query('filter_by');

              // Tentukan tanggal berdasarkan filter
              switch ($filterBy) {
                case 'month':
                    $startDate = Carbon::now()->subMonth();
                    break;
                case 'year':
                    $startDate = Carbon::now()->subYear();
                    break;
                case 'week':
                    $startDate = Carbon::now()->subWeek();
                    break;
                default:
                    $startDate = null; // Menampilkan semua data jika tidak ada filter
                    break;
            }

            $query = Oven::orderBy('sumber_batok')
                        ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            $oven = $query->get();

            if ($oven->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => []], 200);
            }

            $tanggalDitambahkan = $oven->first()->tanggal;

            //* PERSENTASE PENGOVENAN
            $jumlahOvenMasuk = $oven->where('jenis_masukan', 'Penambahan')->sum('pengovenan');
            $jumlahOvenKeluar = $oven->where('jenis_masukan', 'Pengurangan')->sum('pengovenan');

            $totalOven = $jumlahOvenMasuk + $jumlahOvenKeluar;

            if ($totalOven > 0) {
                $persentaseOvenMasuk = ($jumlahOvenMasuk / $totalOven) * 100;
                $persentaseOvenKeluar = ($jumlahOvenKeluar / $totalOven) * 100;
            } else {
                $persentaseOvenMasuk = 0;
                $persentaseOvenKeluar = 0;
            }

            //* PERSENTASE PENDINGINAN
            $jumlahPendinginanMasuk = $oven->where('jenis_masukan', 'Penambahan')->sum('pengovenan');
            $jumlahPendinginanKeluar = $oven->where('jenis_masukan', 'Pengurangan')->sum('pengovenan');

            $totalPendinginan = $jumlahPendinginanMasuk + $jumlahOvenKeluar;

            if ($totalPendinginan > 0) {
                $persentasePendinginanMasuk = ($jumlahPendinginanMasuk / $totalPendinginan) * 100;
                $persentasePendinginanKeluar = ($jumlahPendinginanKeluar / $totalPendinginan) * 100;
            } else {
                $persentasePendinginanMasuk = 0;
                $persentasePendinginanKeluar = 0;
            }

            $lisPersentase = [
                'persentase_oven_masuk' => $persentaseOvenMasuk,
                'persentase_oven_keluar' => $persentaseOvenKeluar,
                'persentase_pendinginan_masuk' => $persentasePendinginanMasuk,
                'persentase_pendinginan_keluar' => $persentasePendinginanKeluar,
            ];

            $response[] = [
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_persentase' => $lisPersentase,
                'list_oven' => $oven,
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

    public function store(Request $request){
        $data = $request->only(
            "jenis_masukan",
            "tanggal",
            "sumber_batok",
            "jenis_briket",
            "pengovenan",
            "pendinginan",
            "keterangan"
        );

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'jenis_briket' => 'required|string',
            'pengovenan' => 'required|numeric',
            'pendinginan' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $oven = Oven::create([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'jenis_briket' => $request->jenis_briket,
                'pengovenan' => $request->pengovenan,
                'pendinginan' => $request->pendinginan,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $oven->id,
                'jenis_masukan' => $oven->jenis_masukan,
                'tanggal' => $oven->tanggal,
                'sumber_batok' => $oven->sumber_batok,
                'jenis_briket' => $oven->jenis_briket,
                'pengovenan' => $oven->pengovenan,
                'pendinginan' => $oven->pendinginan,
                'keterangan' => $oven->keterangan
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        $oven = Oven::find($id);

        $data = $request->only(
            "jenis_masukan",
            "tanggal",
            "sumber_batok",
            "jenis_briket",
            "pengovenan",
            "pendinginan",
            "keterangan"
        );

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'jenis_briket' => 'required|string',
            'pengovenan' => 'required|numeric',
            'pendinginan' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $oven->update([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'jenis_briket' => $request->jenis_briket,
                'pengovenan' => $request->pengovenan,
                'pendinginan' => $request->pendinginan,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $oven], $statusCode);

        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function delete($id) {
        $oven = Oven::find($id);

         if (!$oven) {
             return response()->json(['message' => 'Data not found'], 404);
         }

         DB::beginTransaction();

         try {
            $oven->delete();

             DB::commit();

             return response()->json(['message' => 'Data deleted successfully'], 200);
         } catch (\Throwable $th) {
             DB::rollback();
             return response()->json(['message' => $th->getMessage()], 500);
         }
    }

    public function show($id) {

        try {
           $oven = Oven::find($id);

            if (!$oven) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' =>$oven], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

}
