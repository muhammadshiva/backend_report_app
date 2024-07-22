<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Oven;
use App\Exports\OvenExport;
use Maatwebsite\Excel\Excel;
use Carbon\Carbon;

class OvenController extends Controller
{
    public function index(Request $request){
        try {
            // Dapatkan parameter filter_by dari request
            $filter = $request->query('filter');

            $startDate = null;
            $oven = null;

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
                        $oven = $f;
                    }
                }
            }

            // Ambil data bahan baku berdasarkan tanggal yang difilter
            $query = Oven::orderBy('sumber_batok')
                          ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($oven) {
                $query->where('sumber_batok', 'LIKE', '%' . $oven . '%');
            }

            $oven = $query->get();

            if ($oven->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => new \stdClass()], 200);
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
            $jumlahPendinginanMasuk = $oven->where('jenis_masukan', 'Penambahan')->sum('pendinginan');
            $jumlahPendinginanKeluar = $oven->where('jenis_masukan', 'Pengurangan')->sum('pendinginan');

            $totalPendinginan = $jumlahPendinginanMasuk + $jumlahOvenKeluar;

            if ($totalPendinginan > 0) {
                $persentasePendinginanMasuk = ($jumlahPendinginanMasuk / $totalPendinginan) * 100;
                $persentasePendinginanKeluar = ($jumlahPendinginanKeluar / $totalPendinginan) * 100;
            } else {
                $persentasePendinginanMasuk = 0;
                $persentasePendinginanKeluar = 0;
            }

            $totalData = $oven->count('jumlah');

            // Tambahkan list_data ke setiap item batok
            $oven->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => 'Pengovenan',
                        'jumlah' => $item->pengovenan,
                    ],
                    [
                        'jenis_data' => 'Pendinginan',
                        'jumlah' => $item->pendinginan,
                    ],
                ];
                return $item;
            });


            $listPersentase = [
                [
                    "jenis_persentase" => "Pengovenan",
                    'persentase' => $persentaseOvenMasuk,
                ],
                [
                    "jenis_persentase" => "Pendinginan",
                    'persentase' => $persentasePendinginanMasuk,
                ],
                // 'persentase_oven_masuk' => $persentaseOvenMasuk,
                // 'persentase_oven_keluar' => $persentaseOvenKeluar,
                // 'persentase_pendinginan_masuk' => $persentasePendinginanMasuk,
                // 'persentase_pendinginan_keluar' => $persentasePendinginanKeluar,
            ];

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_persentase' => $listPersentase,
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

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $response], $statusCode);

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
           $oven = Oven::find($id);

            if (!$oven) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' =>$oven], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

    public function exportOvenData(Request $request){
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

            $query = Oven::orderBy('sumber_batok')
            ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($sumberBatok) {
                $query->where('sumber_batok', 'LIKE', '%' . $sumberBatok . '%');
            }

            $oven = $query->get();

            $exportData = $oven->map(function ($item) {
                return [
                    'id' => $item->id,
                    'jenis_masukan' => $item->jenis_masukan,
                    'tanggal' => $item->tanggal,
                    'sumber_batok' => $item->sumber_batok,
                    'jenis_briket' => $item->jenis_briket,
                    'pengovenan' => $item->pengovenan,
                    'pendinginan' => $item->pendinginan,
                    'keterangan' => $item->keterangan,
                ];
            });

            return Excel::download(new OvenExport($exportData), 'oven.xlsx');
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

}
