<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\BahanBaku;
use App\Models\SumberBatok;
use App\Exports\BahanBakuExport;
use Maatwebsite\Excel\Excel;
use Carbon\Carbon;


class BahanBakuController extends Controller
{
    public function index(Request $request) {
        try {
            // Dapatkan parameter filter_by dari request
            $filter = $request->query('filter');

            $startDate = null;
            $bahanBaku = null;

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
                        $bahanBaku = $f;
                    }
                }
            }

            // Ambil data bahan baku berdasarkan tanggal yang difilter
            $query = BahanBaku::orderBy('sumber_batok')
                          ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($bahanBaku) {
                $query->where('bahan_baku', 'LIKE', '%' . $bahanBaku . '%');
            }

            $bahanBaku = $query->get();

            if ($bahanBaku->isEmpty()) {
                return response()->json(['status' => 200, 'message' => 'No data found', 'data' => new \stdClass()], 200);
            }

            $tanggalDitambahkan = $bahanBaku->first()->tanggal;

            //* PERSENTASE ARANG
            $jumlahArangMasuk = $bahanBaku->where('jenis_masukan', 'Penambahan')->where('bahan_baku', 'Stok Arang')->sum('jumlah');
            $jumlahArangKeluar = $bahanBaku->where('jenis_masukan', 'Pengurangan')->where('bahan_baku', 'Stok Arang')->sum('jumlah');

            $totalArang = $jumlahArangMasuk + $jumlahArangKeluar;

            if ($totalArang > 0) {
                $persentaseArangMasuk =  round(($jumlahArangMasuk / $totalArang) * 100, 2);
                $persentaseArangKeluar =  round(($jumlahArangKeluar / $totalArang) * 100, 2);
            } else {
                $persentaseArangMasuk = 0;
                $persentaseArangKeluar = 0;
            }

            //* PERSENTASE ACI
            $jumlahAciMasuk = $bahanBaku->where('jenis_masukan', 'Penambahan')->where('bahan_baku', 'Stok Aci')->sum('jumlah');
            $jumlahAciKeluar = $bahanBaku->where('jenis_masukan', 'Pengurangan')->where('bahan_baku', 'Stok Aci')->sum('jumlah');

            $totalArang = $jumlahAciMasuk + $jumlahAciKeluar;

            if ($totalArang > 0) {
                $persentaseAciMasuk =  round(($jumlahAciMasuk / $totalArang) * 100, 2);
                $persentaseAciKeluar =  round(($jumlahAciKeluar / $totalArang) * 100, 2);
            } else {
                $persentaseAciMasuk = 0;
                $persentaseAciKeluar = 0;
            }

            //* PERSENTASE CAIRAN
            $jumlahCairanMasuk = $bahanBaku->where('jenis_masukan', 'Penambahan')->where('bahan_baku', 'Stok Cairan')->sum('jumlah');
            $jumlahCairanKeluar = $bahanBaku->where('jenis_masukan', 'Pengurangan')->where('bahan_baku', 'Stok Cairan')->sum('jumlah');

            $totalArang = $jumlahCairanMasuk + $jumlahCairanKeluar;

            if ($totalArang > 0) {
                $persentaseCairanMasuk = round(($jumlahCairanMasuk / $totalArang) * 100, 2);
                $persentaseCairanKeluar = round(($jumlahCairanKeluar / $totalArang) * 100, 2);
            } else {
                $persentaseCairanMasuk = 0;
                $persentaseCairanKeluar = 0;
            }

            $totalData = $bahanBaku->count('jumlah');

            // Tambahkan list_data ke setiap item batok
            $bahanBaku->transform(function ($item) {
                $item->list_data = [
                    [
                        'jenis_data' => $item->jenis_masukan == 'Penambahan' ? 'Masuk' : 'Keluar',
                        'jumlah' => $item->jumlah,
                    ],
                ];
                return $item;
            });

            $listPersentase = [
                [
                    "jenis_persentase" => "Stok Aci",
                    'persentase' => $persentaseAciMasuk,
                ],
                [
                    "jenis_persentase" => "Stok Arang",
                    'persentase' => $persentaseArangMasuk,
                ],
                [
                    "jenis_persentase" => "Stok Cairan",
                    'persentase' => $persentaseCairanMasuk,
                ],
                //* BACKUP
                // 'persentase_arang_masuk' => $persentaseArangMasuk,
                // 'persentase_arang_keluar' => $persentaseArangKeluar,
                // 'persentase_aci_masuk' => $persentaseAciMasuk,
                // 'persentase_aci_keluar' => $persentaseAciKeluar,
                // 'persentase_cairan_masuk' => $persentaseCairanMasuk,
                // 'persentase_cairan_keluar' => $persentaseCairanKeluar,
            ];

            $sumberBatokList = SumberBatok::get()->pluck('sumber_batok');

            $response = [
                'total_data' => $totalData,
                'tanggal_ditambahkan' => $tanggalDitambahkan,
                'list_persentase' => $listPersentase,
                'list_sumber_batok'=> $sumberBatokList,
                'list_bahan_baku' => $bahanBaku,
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
            'jenis_masukan',
            'tanggal',
            'sumber_batok',
            'bahan_baku',
            'jumlah',
            'keteragan'
        );

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'bahan_baku' => 'required|string',
            'jumlah' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if($validator->fails()){
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $bahanBaku = BahanBaku::create([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'bahan_baku' => $request->bahan_baku,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'id' => $bahanBaku->id,
                'tanggal' => $bahanBaku->tanggal,
                'sumber_batok' => $bahanBaku->sumber_batok,
                'bahan_baku' => $bahanBaku->bahan_baku,
                'jumlah' => $bahanBaku->jumlah,
                'keterangan' => $bahanBaku->keterangan,
                'updated_at' => $bahanBaku->updated_at,
                'created_at' => $bahanBaku->created_at,
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
            'jenis_masukan',
            'tanggal',
            'sumber_batok',
            'bahan_baku',
            'jumlah',
            'keteragan');

        $validator = Validator::make($data, [
            'jenis_masukan' => 'required|string',
            'tanggal' => 'required|date',
            'sumber_batok' => 'required|string',
            'bahan_baku' => 'required|string',
            'jumlah' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->messages()], 400);
        }

        DB::beginTransaction();

        try {
            $bahanBaku = BahanBaku::findOrFail($id);

            $bahanBaku->update([
                'jenis_masukan' => $request->jenis_masukan,
                'tanggal' => $request->tanggal,
                'sumber_batok' => $request->sumber_batok,
                'bahan_baku' => $request->bahan_baku,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            $response = [
                'id' => $bahanBaku->id,
                'jenis_masukan' => $bahanBaku->jenis_masukan,
                'tanggal' => $bahanBaku->tanggal,
                'sumber_batok' => $bahanBaku->sumber_batok,
                'bahan_baku' => $bahanBaku->bahan_baku,
                'jumlah' => $bahanBaku->jumlah,
                'keterangan' => $bahanBaku->keterangan,
                'updated_at' => $bahanBaku->updated_at,
                'created_at' => $bahanBaku->created_at,
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
        $bahanBaku = BahanBaku::find($id);

        if (!$bahanBaku) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        DB::beginTransaction();

        try {
            $bahanBaku->delete();

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
            $bahanBaku = BahanBaku::find($id);

            if(!$bahanBaku){
                return response()->json(['message' => 'Data not found'], 404);
            }

            return response()->json(['data' => $bahanBaku], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function exportBahanBakuData(Request $request){
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

            $query = BahanBaku::orderBy('sumber_batok')
            ->orderBy('tanggal', 'desc');

            if ($startDate) {
                $query->where('tanggal', '>=', $startDate);
            }

            if ($sumberBatok) {
                $query->where('sumber_batok', 'LIKE', '%' . $sumberBatok . '%');
            }

            $bahanBaku = $query->get();

            $exportData = $bahanBaku->map(function ($item) {
                return [
                    'id' => $item->id,
                    'jenis_masukan' => $item->jenis_masukan,
                    'tanggal' => $item->tanggal,
                    'sumber_batok' => $item->sumber_batok,
                    'jumlah_batok' => $item->jumlah,
                    'keterangan' => $item->keterangan,
                ];
            });

            return Excel::download(new BahanBakuExport($exportData), 'bahan_baku.xlsx');
        } catch (\Throwable $th) {
            $statusCode = 500;
            $message = 'Internal server error';
            return response()->json(['status' => $statusCode, 'message' => $message, 'error' => $th->getMessage()], $statusCode);
        }
    }

}
