<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Mixing;
use App\Exports\MixingExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;


class MixingController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Dapatkan parameter filter_by dari request
            $filter = $request->query('filter');

            $startDate = null;
            $mixing = null;

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
                        $mixing = $f;
                    }
                }
            }

            // Ambil data bahan baku berdasarkan tanggal yang difilter
            $query = Mixing::orderBy('ukuran_pisau')
                ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($mixing) {
                $query->where('ukuran_pisau', 'LIKE', '%' . $mixing . '%');
            }

            $mixing = $query->get();

            if ($mixing->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => new \stdClass()], 200);
            }

            $tanggalDitambahkan = $mixing->first()->tanggal;

            $mixing->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => 'Aci',
                        'jumlah' => $item->jumlah_aci,
                    ],
                    [
                        'jenis_data' => 'Cairan',
                        'jumlah' => $item->jumlah_cairan,
                    ],
                    [
                        'jenis_data' => 'Arang Sulawesi',
                        'jumlah' => $item->jumlah_arang_sulawesi,
                    ],
                    [
                        'jenis_data' => 'Arang Sumatera',
                        'jumlah' => $item->jumlah_arang_sumatera,
                    ],
                    [
                        'jenis_data' => 'Arang Kayu',
                        'jumlah' => $item->jumlah_arang_kayu,
                    ],
                ];
                return $item;
            });

            $totalData = $mixing->count('ukuran_pisau');

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
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

    public function store(Request $request)
    {
        $data = $request->only(
            'tanggal',
            'ukuran_pisau',
            'jumlah_aci',
            'jumlah_cairan',
            'jumlah_arang_sulawesi',
            'jumlah_arang_sumatera',
            'jumlah_arang_kayu',
            'keterangan'
        );

        $validator = Validator::make($data, [
            'tanggal' => 'required|date',
            'ukuran_pisau' => 'required|numeric',
            'jumlah_aci' => 'required|numeric',
            'jumlah_cairan' => 'required|numeric',
            'jumlah_arang_sulawesi' => 'required|numeric',
            'jumlah_arang_sumatera' => 'required|numeric',
            'jumlah_arang_kayu' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $mixing = Mixing::create([
                'tanggal' => $request->tanggal,
                'ukuran_pisau' => $request->ukuran_pisau,
                'jumlah_aci' => $request->jumlah_aci,
                'jumlah_cairan' => $request->jumlah_cairan,
                'jumlah_arang_sulawesi' => $request->jumlah_arang_sulawesi,
                'jumlah_arang_sumatera' => $request->jumlah_arang_sumatera,
                'jumlah_arang_kayu' => $request->jumlah_arang_kayu,
                'keterangan' => $request->keterangan
            ]);

            DB::commit();

            $response = [
                'id' => $mixing->id,
                'tanggal' => $mixing->tanggal,
                'ukuran_pisau' => $mixing->ukuran_pisau,
                'jumlah_arang_sulawesi' => $mixing->jumlah_arang_sulawesi,
                'jumlah_arang_sumatera' => $mixing->jumlah_arang_sumatera,
                'jumlah_arang_kayu' => $mixing->jumlah_arang_kayu,
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

    public function update(Request $request, $id)
    {
        $mixing = Mixing::find($id);

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'jumlah_aci' => 'required|numeric',
            'jumlah_cairan' => 'required|numeric',
            'ukuran_pisau' => 'required|numeric',
            'jumlah_arang_sulawesi' => 'required|numeric',
            'jumlah_arang_sumatera' => 'required|numeric',
            'jumlah_arang_kayu' => 'required|numeric',
            'keterangan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $mixing->update([
                'tanggal' => $request->tanggal,
                'ukuran_pisau' => $request->ukuran_pisau,
                'jumlah_aci' => $request->jumlah_aci,
                'jumlah_cairan' => $request->jumlah_cairan,
                'jumlah_arang_sulawesi' => $request->jumlah_arang_sulawesi,
                'jumlah_arang_sumatera' => $request->jumlah_arang_sumatera,
                'jumlah_arang_kayu' => $request->jumlah_arang_kayu,
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

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $mixing = Mixing::findOrFail($id);

            $mixing->delete();

            DB::commit();

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => new \stdClass()], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $mixing = Mixing::find($id);

            if (!$mixing) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $mixing], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function exportMixingData(Request $request)
    {
        try {
            // Dapatkan parameter filter dari request
            $filter = $request->query('filter');

            $startDate = null;
            $ukuranPisau = null;

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
                        $ukuranPisau = $f;
                    }
                }
            }

            $query = Mixing::orderBy('ukuran_pisau')
                ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($ukuranPisau) {
                $query->where('ukuran_pisau', 'LIKE', '%' . $ukuranPisau . '%');
            }

            $mixing = $query->get();

            $exportData = $mixing->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal' => $item->tanggal,
                    'ukuran_pisau' => $item->ukuran_pisau,
                    'jumlah_aci' => $item->jumlah_aci,
                    'jumlah_cairan' => $item->jumlah_cairan,
                    'jumlah_arang_sulawesi' => $item->jumlah_arang_sulawesi,
                    'jumlah_arang_sumatera' => $item->jumlah_arang_sumatera,
                    'jumlah_arang_kayu' => $item->jumlah_arang_kayu,
                    'keterangan' => $item->keterangan,
                ];
            });

            return Excel::download(new MixingExport($exportData), 'mixing.xlsx');
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }
}
