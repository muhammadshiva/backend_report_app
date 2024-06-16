<?php

namespace App\Exports;

use App\Models\Batok;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class BatokExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    // public function collection()
    // {
    //     return Batok::all();
    // }

    protected $exportData;

    public function __construct(Collection $exportData){
        $this->exportData = $exportData;
    }

    public function collection(){
        return $this->exportData;
    }

    public function headings(): array {
        return [
            'ID',
            'Jenis Masukan',
            'Tanggal',
            'Sumber Batok',
            'Jumlah Batok',
            'Keterangan'
        ];
    }
}
