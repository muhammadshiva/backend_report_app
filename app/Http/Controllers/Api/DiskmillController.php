<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Diskmill;

class DiskmillController extends Controller
{
    public function index(){
        try {
            $diskmill = Diskmill::all();
            return response()->json(['data' => $diskmill], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'batok_masuk',
            'hasil_pisau_02',
            'hasil_pisau_03',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'batok_masuk' => 'required|numeric',
            'hasil_pisau_02' => 'required|numeric',
            'hasil_pisau_03' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $diskmill = Diskmill::create([
                'tanggal' => $request->tanggal,
                'batok_masuk' => $request->batok_masuk,
                'hasil_pisau_02' => $request->hasil_pisau_02,
                'hasil_pisau_03' => $request->hasil_pisau_03,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'id' => $diskmill->id,
                'tanggal' => $diskmill->tanggal,
                'batok_masuk' => $diskmill->batok_masuk,
                'hasil_pisau_02' => $diskmill->hasil_pisau_02,
                'hasil_pisau_03' => $diskmill->hasil_pisau_03,
                'keterangan' => $diskmill->keterangan,
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){

        $diskmill = Diskmill::find($id);

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'batok_masuk' => 'required|numeric',
            'hasil_pisau_02' => 'required|numeric',
            'hasil_pisau_03' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $diskmill->update([
                'tanggal' => $request->tanggal,
                'batok_masuk' => $request->batok_masuk,
                'hasil_pisau_02' => $request->hasil_pisau_02,
                'hasil_pisau_03' => $request->hasil_pisau_03,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json(['data' => $diskmill], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
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
