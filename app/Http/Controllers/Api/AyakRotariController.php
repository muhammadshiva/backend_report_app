<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\AyakRotari;

class AyakRotariController extends Controller
{
    public function index(){
        try {
            $ayakRotari = AyakRotari::all();
            return response()->json(['data' => $ayakRotari], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'batok_masuk',
            'batok_kotor',
            'hasil_batok',
            'hasil_abu',
            'keterangan',
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'batok_masuk' => 'required|numeric',
            'batok_kotor' => 'required|numeric',
            'hasil_batok' => 'required|numeric',
            'hasil_abu' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $ayakRotari = AyakRotari::create([
                'tanggal' => $request->tanggal,
                'batok_masuk' => $request->batok_masuk,
                'batok_kotor' => $request->batok_kotor,
                'hasil_batok' => $request->hasil_batok,
                'hasil_abu' => $request->hasil_abu,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $ayakRotari->id,
                'tanggal' => $ayakRotari->tanggal,
                'batok_masuk' => $ayakRotari->batok_masuk,
                'batok_kotor' => $ayakRotari->batok_kotor,
                'hasil_batok' => $ayakRotari->hasil_batok,
                'hasil_abu' => $ayakRotari->hasil_abu,
                'keterangan' => $ayakRotari->keterangan
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
       $ayakRotari = AyakRotari::find($id);

        if (!$ayakRotari) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'batok_masuk' => 'required|numeric',
            'batok_kotor' => 'required|numeric',
            'hasil_batok' => 'required|numeric',
            'hasil_abu' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
           $ayakRotari->update([
                'tanggal' => $request->tanggal,
                'batok_masuk' => $request->batok_masuk,
                'batok_kotor' => $request->batok_kotor,
                'hasil_batok' => $request->hasil_batok,
                'hasil_abu' => $request->hasil_abu,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            return response()->json(['data' =>$ayakRotari], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete($id) {
       $ayakRotari = AyakRotari::find($id);

        if (!$ayakRotari) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();

        try {
           $ayakRotari->delete();

            DB::commit();

            return response()->json(['message' => 'Data deleted successfully'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id) {

        try {
           $ayakRotari = AyakRotari::find($id);

            if (!$ayakRotari) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' =>$ayakRotari], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

}
