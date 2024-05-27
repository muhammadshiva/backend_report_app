<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Briket;

class BriketController extends Controller
{

    public function index(){
        try {
            $briket = Briket::all();
            return response()->json(['data' => $briket], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'jenis_briket',
            'stok_awal',
            'stok_akhir',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'jenis_briket' => 'required|string',
            'stok_awal' => 'required|numeric',
            'stok_akhir' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $briket = Briket::create([
                'tanggal' => $request->tanggal,
                'jenis_briket' => $request->jenis_briket,
                'stok_awal' => $request->stok_awal,
                'stok_akhir' => $request->stok_akhir,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'data' => [
                    'id' => $briket->id,
                    'tanggal' => $briket->tanggal,
                    'jenis_briket' => $briket->jenis_briket,
                    'stok_awal' => $briket->stok_awal,
                    'stok_akhir' => $briket->stok_akhir,
                    'keterangan' => $briket->keterangan
                ]
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        $briket = Briket::find($id);

        // Jika data tidak ditemukan, kembalikan respons dengan status 404 (Not Found)
        if (!$briket) {
            return response()->json(['message' => 'Data not found'], 404);
        }


        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'jenis_briket' => 'required|string',
            'stok_awal' => 'required|numeric',
            'stok_akhir' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $briket->update([
                'tanggal' => $request->tanggal,
                'jenis_briket' => $request->jenis_briket,
                'stok_awal' => $request->stok_awal,
                'stok_akhir' => $request->stok_akhir,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $briket->id,
                'tanggal' => $briket->tanggal,
                'jenis_briket' => $briket->jenis_briket,
                'stok_awal' => $briket->stok_awal,
                'stok_akhir' => $briket->stok_akhir,
                'keterangan' => $briket->keterangan
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }


    public function delete($id) {
        $briket = Briket::find($id);

         if (!$briket) {
             return response()->json(['message' => 'Data not found'], 404);
         }

         DB::beginTransaction();

         try {
            $briket->delete();

             DB::commit();

             return response()->json(['message' => 'Data deleted successfully'], 200);
         } catch (\Throwable $th) {
             DB::rollback();
             return response()->json(['message' => $th->getMessage()], 500);
         }
    }

    public function show($id) {

        try {
           $briket = Briket::find($id);

            if (!$briket) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' =>$briket], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

}
