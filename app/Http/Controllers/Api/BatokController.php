<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Batok;
use App\Models\SumberBatok;
use App\Exports\BatokExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class BatokController extends Controller
{

    public function index(Request $request)
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

            // Ambil data batok berdasarkan tanggal yang difilter
            $query = Batok::orderBy('sumber_batok')
                ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($sumberBatok) {
                $query->where('sumber_batok', 'LIKE', '%' . $sumberBatok . '%');
            }

            $batok = $query->get();

            if ($batok->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => []], 200);
            }

            $tanggalDitambahkan = $batok->first()->tanggal;

            $totalData = $batok->count('jumlah_batok');
            $jumlahBatokMasuk = $batok->where('jenis_masukan', 'Penambahan')->sum('jumlah_batok');
            $jumlahBatokKeluar = $batok->where('jenis_masukan', 'Pengurangan')->sum('jumlah_batok');

            $totalBatok = $jumlahBatokMasuk + $jumlahBatokKeluar;

            if ($totalBatok > 0) {
                $persentaseBatokMasuk = round(($jumlahBatokMasuk / $totalBatok) * 100, 2);
                $persentaseBatokKeluar = round(($jumlahBatokKeluar / $totalBatok) * 100, 2);
            } else {
                $persentaseBatokMasuk = 0;
                $persentaseBatokKeluar = 0;
            }

            $listPersentase = [
                [
                    'jenis_persentase' => 'Batok Masuk',
                    'persentase' => $jumlahBatokMasuk,
                ],
                [
                    'jenis_persentase' => 'Batok Keluar',
                    'persentase' => $jumlahBatokKeluar,
                ]
            ];

            $sumberBatokList = SumberBatok::get()->pluck('sumber_batok');

            // Tambahkan list_data ke setiap item batok
            $batok->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => 'Batok',
                        'jumlah' => $item->jumlah_batok,
                    ],
                ];
                return $item;
            });

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_persentase' => $listPersentase,
                'list_sumber_batok' => $sumberBatokList,
                'list_batok' => $batok,
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

    public function showByResource($resource)
    {
        try {
            // Fetch all Batok records with the specified sumber_batok
            $batokList = Batok::where('sumber_batok', 'LIKE', '%' . $resource . '%')->get();

            if ($batokList->isEmpty()) {
                $statusCode = 404;
                $message = 'Data not found here';
                return response()->json(['status' => $statusCode, 'message' => $message, 'data' => new \stdClass()], $statusCode);
            }

            // Calculate total and percentages
            $totalBarangMasuk = $batokList->sum('barang_masuk');
            $totalBarangKeluar = $batokList->sum('barang_keluar');
            $totalStokAwal = $batokList->sum('stok_awal');
            $totalStokAkhir = $batokList->sum('stok_akhir');

            $persentaseBarang = $totalBarangMasuk ? ($totalBarangKeluar / $totalBarangMasuk) * 100 : 0;
            $persentaseStok = $totalStokAwal ? ($totalStokAkhir / $totalStokAwal) * 100 : 0;

            // Find the latest date
            $tanggalDitambahkan = $batokList->max('tanggal');

            $response = [
                'sumber_batok' => $resource,
                'persentase_barang' => $persentaseBarang,
                'persentase_stok' => $persentaseStok,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_batok' => $batokList,
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

        $data = $request->only('jenis_masukan', 'tanggal', 'sumber_batok', 'jumlah_batok', 'keterangan');

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'jumlah_batok' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $batok = Batok::create([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'jumlah_batok' => $request->jumlah_batok,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'jenis_masukan' => $batok->jenis_masukan,
                'tanggal' => $batok->tanggal,
                'sumber_batok' => $batok->sumber_batok,
                'jumlah_batok' => $batok->jumlah_batok,
                'keterangan' => $batok->keterangan,
                'updated_at' => $batok->updated_at,
                'created_at' => $batok->created_at,
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
        $data = $request->only('jenis_masukan', 'tanggal', 'sumber_batok', 'jumlah_batok', 'keterangan');

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'jumlah_batok' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $batok = Batok::findOrFail($id);

            $batok->update([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'jumlah_batok' => $request->jumlah_batok,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'id' => $batok->id,
                'jenis_masukan' => $batok->jenis_masukan,
                'tanggal' => $batok->tanggal,
                'sumber_batok' => $batok->sumber_batok,
                'jumlah_batok' => $batok->jumlah_batok,
                'keterangan' => $batok->keterangan,
                'updated_at' => $batok->updated_at,
                'created_at' => $batok->created_at,
            ];

            $statusCode = 200;
            $message = 'Success';
            return response()->json(['status' => $statusCode, 'message' => $message, 'data' => $response], $statusCode);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $batok = Batok::findOrFail($id);

            $batok->delete();

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
            $batok = Batok::find($id);

            if (!$batok) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $batok], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function exportBatokData(Request $request)
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

            $query = Batok::orderBy('sumber_batok')
                ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($sumberBatok) {
                $query->where('sumber_batok', 'LIKE', '%' . $sumberBatok . '%');
            }

            $batok = $query->get();

            $exportData = $batok->map(function ($item) {
                return [
                    'id' => $item->id,
                    'jenis_masukan' => $item->jenis_masukan,
                    'tanggal' => $item->tanggal,
                    'sumber_batok' => $item->sumber_batok,
                    'jumlah_batok' => $item->jumlah_batok,
                    'keterangan' => $item->keterangan,
                ];
            });

            return Excel::download(new BatokExport($exportData), 'batok_data.xlsx');
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }
}
