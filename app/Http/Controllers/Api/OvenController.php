<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Oven;

class OvenController extends Controller
{
    public function index(){
        try {
            $oven = Oven::all();
            return response()->json(['data' => $oven], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'jenis_briket',
            'pendinginan_awal',
            'pendinginan_akhir',
            'pengovenan_masuk',
            'pengovenan_keluar',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'jenis_briket' => 'required|string',
            'pendinginan_awal' => 'required|numeric',
            'pendinginan_akhir' => 'required|numeric',
            'pengovenan_masuk' => 'required|numeric',
            'pengovenan_keluar' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $oven = Oven::create([
                'tanggal' => $request->tanggal,
                'jenis_briket' => $request->jenis_briket,
                'pendinginan_awal' => $request->pendinginan_awal,
                'pendinginan_akhir' => $request->pendinginan_akhir,
                'pengovenan_masuk' => $request->pengovenan_masuk,
                'pengovenan_keluar' => $request->pengovenan_keluar,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $oven->id,
                'tanggal' => $request->tanggal,
                'jenis_briket' => $request->jenis_briket,
                'pendinginan_awal' => $request->pendinginan_awal,
                'pendinginan_akhir' => $request->pendinginan_akhir,
                'pengovenan_masuk' => $request->pengovenan_masuk,
                'pengovenan_keluar' => $request->pengovenan_keluar,
                'keterangan' => $request->keterangan
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        $oven = Oven::find($id);

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'jenis_briket' => 'required|string',
            'pendinginan_awal' => 'required|numeric',
            'pendinginan_akhir' => 'required|numeric',
            'pengovenan_masuk' => 'required|numeric',
            'pengovenan_keluar' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $oven->update([
                'tanggal' => $request->tanggal,
                'jenis_briket' => $request->jenis_briket,
                'pendinginan_awal' => $request->pendinginan_awal,
                'pendinginan_akhir' => $request->pendinginan_akhir,
                'pengovenan_masuk' => $request->pengovenan_masuk,
                'pengovenan_keluar' => $request->pengovenan_keluar,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            return response()->json(['data' => $oven], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
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
