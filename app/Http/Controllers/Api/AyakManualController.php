<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\AyakManual;
use App\Exports\AyakManualExport;
use Maatwebsite\Excel\Excel;
use Carbon\Carbon;


class AyakManualController extends Controller
{
    public function index(Request $request) {
        try {
             // Dapatkan parameter filter_by dari request
            $filter = $request->query('filter');

            $startDate = null;
            $ayakManual = null;

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
                        $ayakManual = $f;
                    }
                }
            }
             // Ambil data bahan baku berdasarkan tanggal yang difilter
             $query = AyakManual::orderBy('sumber_batok')
                           ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($ayakManual) {
                $query->where('sumber_batok', 'LIKE', '%' . $ayakManual . '%');
            }

             $ayakManual = $query->get();

            if ($ayakManual->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => new \stdClass()], 200);
            }

            $tanggalDitambahkan = $ayakManual->first()->tanggal;

            $ayakManual->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => 'Batok',
                        'jumlah' => $item->jumlah_batok,
                    ],
                    [
                        'jenis_data' => 'Batok Mentah',
                        'jumlah' => $item->jumlah_batok_mentah,
                    ],
                    [
                        'jenis_data' => 'Granul',
                        'jumlah' => $item->jumlah_granul,
                    ],
                ];
                return $item;
            });

            $totalData = $ayakManual->count('jumlah');

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_ayak_manual' => $ayakManual,
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
            'jumlah_batok',
            'jumlah_batok_mentah',
            'jumlah_granul',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'jumlah_batok' => 'required|numeric',
            'jumlah_batok_mentah' => 'required|numeric',
            'jumlah_granul' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $ayakManual = AyakManual::create([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'jumlah_batok' => $request->jumlah_batok,
                'jumlah_batok_mentah' => $request->jumlah_batok_mentah,
                'jumlah_granul' => $request->jumlah_granul,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $ayakManual->id,
                'sumber_batok' => $request->sumber_batok,
                'jumlah_batok' => $ayakManual->jumlah_batok,
                'jumlah_batok_mentah' => $ayakManual->jumlah_batok_mentah,
                'jumlah_granul' => $ayakManual->jumlah_granul,
                'keterangan' => $ayakManual->keterangan
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
        $data = $request->only(
            'tanggal',
            'sumber_batok',
            'jumlah_batok',
            'jumlah_batok_mentah',
            'jumlah_granul',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'jumlah_batok' => 'required|numeric',
            'jumlah_batok_mentah' => 'required|numeric',
            'jumlah_granul' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $ayakManual = AyakManual::findOrFail($id);

            $ayakManual->update([
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'jumlah_batok' => $request->jumlah_batok,
                'jumlah_batok_mentah' => $request->jumlah_batok_mentah,
                'jumlah_granul' => $request->jumlah_granul,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $ayakManual->id,
                'sumber_batok' => $request->sumber_batok,
                'jumlah_batok' => $ayakManual->jumlah_batok,
                'jumlah_batok_mentah' => $ayakManual->jumlah_batok_mentah,
                'jumlah_granul' => $ayakManual->jumlah_granul,
                'keterangan' => $ayakManual->keterangan
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
        $ayakManual = AyakManual::find($id);

        if (!$ayakManual) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();

        try {
            $ayakManual->delete();

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
            $ayakManual = AyakManual::find($id);

            if (!$ayakManual) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $ayakManual], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

    public function exportAyakManualData(Request $request){
        try {
            // Dapatkan parameter filter dari request
            $filter = $request->query('filter');

            $startDate = null;
            $sumberBatok = null;

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
                        $sumberBatok = $f;
                    }
                }
            }

            $query = AyakManual::orderBy('sumber_batok')
            ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($sumberBatok) {
                $query->where('sumber_batok', 'LIKE', '%' . $sumberBatok . '%');
            }

            $ayakManual = $query->get();

            $exportData = $ayakManual->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal,
                    'sumber_batok' => $item->sumber_batok,
                    'jumlah_batok' => $item->jumlah_batok,
                    'jumlah_batok_mentah' => $item->jumlah_batok_mentah,
                    'jumlah_granul' => $item->jumlah_granul,
                    'keterangan' => $item->keterangan,
                ];
            });

            return Excel::download(new AyakManualExport($exportData), 'ayak_manual.xlsx');
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

}
