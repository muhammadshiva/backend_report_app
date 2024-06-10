<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Diskmill;
use Carbon\Carbon;

class DiskmillController extends Controller
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

             // Ambil data bahan baku berdasarkan tanggal yang difilter
             $query = Diskmill::orderBy('sumber_batok')
                           ->orderBy('tanggal', 'desc');

             if ($startDate) {
                 $query->where('tanggal', '>=', $startDate);
             }

             $diskmill = $query->get();

             if ($diskmill->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => []], 200);
            }

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $diskmill], $statusCode);
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
            'batok_masuk',
            'hasil_pisau_02',
            'hasil_pisau_03',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'batok_masuk' => 'required|numeric',
            'hasil_pisau_02' => 'required|numeric',
            'hasil_pisau_03' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $diskmill = Diskmill::create([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'batok_masuk' => $request->batok_masuk,
                'hasil_pisau_02' => $request->hasil_pisau_02,
                'hasil_pisau_03' => $request->hasil_pisau_03,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'id' => $diskmill->id,
                'tanggal' => $diskmill->tanggal,
                'sumber_batok' => $diskmill->sumber_batok,
                'batok_masuk' => $diskmill->batok_masuk,
                'hasil_pisau_02' => $diskmill->hasil_pisau_02,
                'hasil_pisau_03' => $diskmill->hasil_pisau_03,
                'keterangan' => $diskmill->keterangan,
            ];

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $diskmill], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function update(Request $request, $id){

        $diskmill = Diskmill::find($id);

        $data = $request->only(
            'tanggal',
            'sumber_batok',
            'batok_masuk',
            'hasil_pisau_02',
            'hasil_pisau_03',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'batok_masuk' => 'required|numeric',
            'hasil_pisau_02' => 'required|numeric',
            'hasil_pisau_03' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $diskmill->update([
            'tanggal' => $request->tanggal,
            'sumber_batok' => $request->sumber_batok,
            'batok_masuk' => $request->batok_masuk,
            'hasil_pisau_02' => $request->hasil_pisau_02,
            'hasil_pisau_03' => $request->hasil_pisau_03,
            'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $diskmill], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function delete($id) {
        $diskmill = Diskmill::find($id);

         if (!$diskmill) {
             return response()->json(['message' => 'Data not found'], 404);
         }

         DB::beginTransaction();

         try {
            $diskmill->delete();

             DB::commit();

             return response()->json(['message' => 'Data deleted successfully'], 200);
         } catch (\Throwable $th) {
             DB::rollback();
             return response()->json(['message' => $th->getMessage()], 500);
         }
    }

    public function show($id) {

        try {
           $diskmill = Diskmill::find($id);

            if (!$diskmill) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' =>$diskmill], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }
}
