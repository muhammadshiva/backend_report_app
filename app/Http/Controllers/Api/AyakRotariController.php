<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\AyakRotari;
use Carbon\Carbon;

class AyakRotariController extends Controller
{
    public function index(Request $request){
        try {
            // Dapatkan parameter filter_by dari request
            $filter = $request->query('filter');

            $startDate = null;
            $ayakRotari = null;

            if ($filter) {
                $filters = explode(',', $filter);

                // Parsing filters
                foreach ($filters as $f) {
                    if (in_array($f, ['month', 'year', 'week'])) {
                        switch ($f) {
                            case 'month':
                                $startDate = Carbon::now()->subMonth();
                                break;
                            case 'year':
                                $startDate = Carbon::now()->subYear();
                                break;
                            case 'week':
                                $startDate = Carbon::now()->subWeek();
                                break;
                        }
                    } else {
                        $ayakRotari = $f;
                    }
                }
            }

            // Ambil data bahan baku berdasarkan tanggal yang difilter
            $query = AyakRotari::orderBy('sumber_batok')
            ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($ayakRotari) {
                $query->where('sumber_batok', 'LIKE', '%' . $ayakRotari . '%');
            }

            $ayakRotari = $query->get();

            if ($ayakRotari->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => new \stdClass()], 200);
            }

            $tanggalDitambahkan = $ayakRotari->first()->tanggal;

            $ayakRotari->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => 'Batok Masuk',
                        'jumlah' => $item->batok_masuk,
                    ],
                    [
                        'jenis_data' => 'Batok Kotor',
                        'jumlah' => $item->batok_kotor,
                    ],
                    [
                        'jenis_data' => 'Hasil Batok',
                        'jumlah' => $item->hasil_batok,
                    ],
                    [
                        'jenis_data' => 'Hasil Abu',
                        'jumlah' => $item->hasil_batok,
                    ],
                ];
                return $item;
            });

            $totalData = $ayakRotari->count('sumber_batok');

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_ayak_rotari' => $ayakRotari,
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
            'tanggal',
            'sumber_batok',
            'batok_masuk',
            'batok_kotor',
            'hasil_batok',
            'hasil_abu',
            'keterangan',
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
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
                'sumber_batok' => $request->sumber_batok,
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
                'sumber_batok' => $ayakRotari->sumber_batok,
                'batok_masuk' => $ayakRotari->batok_masuk,
                'batok_kotor' => $ayakRotari->batok_kotor,
                'hasil_batok' => $ayakRotari->hasil_batok,
                'hasil_abu' => $ayakRotari->hasil_abu,
                'keterangan' => $ayakRotari->keterangan
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
       $ayakRotari = AyakRotari::find($id);

        if (!$ayakRotari) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
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
                'sumber_batok' => $request->sumber_batok,
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
                'sumber_batok' => $ayakRotari->sumber_batok,
                'batok_masuk' => $ayakRotari->batok_masuk,
                'batok_kotor' => $ayakRotari->batok_kotor,
                'hasil_batok' => $ayakRotari->hasil_batok,
                'hasil_abu' => $ayakRotari->hasil_abu,
                'keterangan' => $ayakRotari->keterangan
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
       $ayakRotari = AyakRotari::find($id);

        if (!$ayakRotari) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();

        try {
           $ayakRotari->delete();

            DB::commit();

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => new \stdClass()], $statusCode);
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
