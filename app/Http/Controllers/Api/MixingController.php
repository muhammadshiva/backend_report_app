<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Mixing;

class MixingController extends Controller
{
    public function index(){
        try {
            $mixing = Mixing::all();
            return response()->json(['data' => $mixing], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request){
        $data = $request->only(
            'tanggal',
            'ukuran_pisau',
            'jumlah_arang',
            'jumlah_aci',
            'jumlah_cairan',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
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
                'ukuran_pisau' => $mixing->ukuran_pisau,
                'jumlah_arang' => $mixing->jumlah_arang,
                'jumlah_aci' => $mixing->jumlah_aci,
                'jumlah_cairan' => $mixing->jumlah_cairan,
                'keterangan' => $mixing->keterangan
            ];

            return response()->json(['data' => $response], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function update(Request $request, $id){
        $mixing = Mixing::find($id);

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
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
                'ukuran_pisau' => $request->ukuran_pisau,
                'jumlah_arang' => $request->jumlah_arang,
                'jumlah_aci' => $request->jumlah_aci,
                'jumlah_cairan' => $request->jumlah_cairan,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            return response()->json(['data' => $mixing], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
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
