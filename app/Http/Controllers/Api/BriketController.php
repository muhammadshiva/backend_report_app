<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Briket;
use App\Exports\BriketExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BriketController extends Controller
{

    public function index(Request $request)
    {
        try {
            // Dapatkan parameter filter_by dari request
            $filter = $request->query('filter');

            $startDate = null;
            $briket = null;

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
                        $briket = $f;
                    }
                }
            }

            // Ambil data bahan baku berdasarkan tanggal yang difilter
            $query = Briket::orderBy('jenis_briket')
                ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($briket) {
                $query->where('jenis_briket', 'LIKE', '%' . $briket . '%');
            }

            $briket = $query->get();

            if ($briket->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => new \stdClass()], 200);
            }

            $tanggalDitambahkan = $briket->first()->tanggal;

            $jumlahBriketMasuk = $briket->where('jenis_masukan', 'Penambahan')->sum('stok');
            $jumlahBriketKeluar = $briket->where('jenis_masukan', 'Pengurangan')->sum('stok');

            $totalBriket = $jumlahBriketMasuk + $jumlahBriketKeluar;

            if ($totalBriket > 0) {
                $persentaseBriketMasuk = ($jumlahBriketMasuk / $totalBriket) * 100;
                $persentaseBriketKeluar = ($jumlahBriketKeluar / $totalBriket) * 100;
            } else {
                $persentaseBriketMasuk = 0;
                $persentaseBriketKeluar = 0;
            }

            $totalData = $briket->count('jenis_briket');

            $briket->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => 'Stok',
                        'jumlah' => $item->stok,
                    ],
                ];
                return $item;
            });


            $listPersentase = [
                [
                    "jenis_persentase" => "Stok Briket",
                    'persentase' => $jumlahBriketMasuk,
                ],
            ];

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_persentase' => $listPersentase,
                'list_briket' => $briket,
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

    public function store(Request $request)
    {
        $data = $request->only(
            'jenis_masukan',
            'tanggal',
            // 'sumber_batok',
            'jenis_briket',
            'stok',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            // 'sumber_batok' => 'required|string',
            'jenis_briket' => 'required|string',
            'stok' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $briket = Briket::create([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                // 'sumber_batok' => $request->sumber_batok,
                'jenis_briket' => $request->jenis_briket,
                'stok' => $request->stok,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $briket->id,
                'jenis_masukan' => $briket->jenis_masukan,
                'tanggal' => $briket->tanggal,
                // 'sumber_batok' => $briket->sumber_batok,
                'stok' => $briket->stok,
                'keterangan' => $briket->keterangan
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

    public function update(Request $request, $id)
    {
        $briket = Briket::find($id);

        // Jika data tidak ditemukan, kembalikan respons dengan status 404 (Not Found)
        if (!$briket) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $data = $request->only(
            'jenis_masukan',
            'tanggal',
            // 'sumber_batok',
            'jenis_briket',
            'stok',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            // 'sumber_batok' => 'required|string',
            'jenis_briket' => 'required|string',
            'stok' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $briket->update([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                // 'sumber_batok' => $request->sumber_batok,
                'jenis_briket' => $request->jenis_briket,
                'stok' => $request->stok,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $briket->id,
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                // 'sumber_batok' => $request->sumber_batok,
                'jenis_briket' => $request->jenis_briket,
                'stok' => $request->stok,
                'keterangan' => $request->keterangan
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


    public function delete($id)
    {
        $briket = Briket::find($id);

        if (!$briket) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();

        try {
            $briket->delete();

            DB::commit();

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => new \stdClass()], $statusCode);;
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {

        try {
            $briket = Briket::find($id);

            if (!$briket) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $briket], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function exportBriketData(Request $request)
    {
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

            $query = Briket::orderBy('jenis_briket')
                ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($sumberBatok) {
                $query->where('jenis_briket', 'LIKE', '%' . $sumberBatok . '%');
            }

            $briket = $query->get();

            $exportData = $briket->map(function ($item) {
                return [
                    'id' => $item->id,
                    'jenis_masukan' => $item->jenis_masukan,
                    'tanggal' => $item->tanggal,
                    // 'sumber_batok' => $item->sumber_batok,
                    'jenis_briket' => $item->jenis_briket,
                    'stok' => $item->stok,
                    'keterangan' => $item->keterangan,
                ];
            });

            return Excel::download(new BriketExport($exportData), 'briket.xlsx');
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }
}
