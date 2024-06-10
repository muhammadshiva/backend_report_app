<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Mixing;
use Carbon\Carbon;


class MixingController extends Controller
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
            $query = Mixing::orderBy('sumber_batok')
                        ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            $mixing = $query->get();

            if ($mixing->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => []], 200);
            }

            $totalArang = $mixing->sum('jumlah_arang');
            $totalAci = $mixing->sum('jumlah_aci');
            $totalCairan = $mixing->sum('jumlah_cairan');

            $listTotalMixing = [
                'total_arang' => $totalArang,
                'total_aci' => $totalAci,
                'total_cairan' => $totalCairan,
            ];

            $response[] = [
                'list_total_mixing' => $listTotalMixing,
                'list_mixing' => $mixing,
            ];

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
            'ukuran_pisau',
            'jumlah_arang',
            'jumlah_aci',
            'jumlah_cairan',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'ukuran_pisau' => 'required|numeric',
            'jumlah_arang' => 'required|numeric',
            'jumlah_aci' => 'required|numeric',
            'jumlah_cairan' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $mixing = Mixing::create([
                'tanggal' => $request->tanggal,
                'sumber_batok'=> $request->sumber_batok,
                'ukuran_pisau' => $request->ukuran_pisau,
                'jumlah_arang' => $request->jumlah_arang,
                'jumlah_aci' => $request->jumlah_aci,
                'jumlah_cairan' => $request->jumlah_cairan,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $mixing->id,
                'tanggal' => $mixing->tanggal,
                'sumber_batok' => $mixing->sumber_batok,
                'ukuran_pisau' => $mixing->ukuran_pisau,
                'jumlah_arang' => $mixing->jumlah_arang,
                'jumlah_aci' => $mixing->jumlah_aci,
                'jumlah_cairan' => $mixing->jumlah_cairan,
                'keterangan' => $mixing->keterangan
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

    public function update(Request $request, $id){
        $mixing = Mixing::find($id);

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'ukuran_pisau' => 'required|numeric',
            'jumlah_arang' => 'required|numeric',
            'jumlah_aci' => 'required|numeric',
            'jumlah_cairan' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $mixing->update([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'ukuran_pisau' => $request->ukuran_pisau,
                'jumlah_arang' => $request->jumlah_arang,
                'jumlah_aci' => $request->jumlah_aci,
                'jumlah_cairan' => $request->jumlah_cairan,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $mixing], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

    public function delete($id) {
        DB::beginTransaction();

        try {
            $mixing = Mixing::findOrFail($id);

            $mixing->delete();

            DB::commit();

            return response()->json(['message' => 'Data Batok berhasil dihapus'], 200);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id) {
        try {
            $mixing = Mixing::find($id);

            if(!$mixing){
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $mixing], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
