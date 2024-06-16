<?php

namespace App\Exports;

use App\Models\Diskmill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class DiskmillExport implements FromCollection, WithHeadings
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
            'Batok Masuk',
            'Hasil Pisau 02',
            'Hasil Pisau 03',
            'Keterangan'
        ];
    }
}
