<?php

namespace App\Exports;

use App\Models\Mixing;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class MixingExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
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
            'Tanggal',
            'Sumber Batok',
            'Ukuran Pisau',
            'Jumlah Arang',
            'Jumlah Aci',
            'Jumlah Cairan',
            'Keterangan'
        ];
    }
}
