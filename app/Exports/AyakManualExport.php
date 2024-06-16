<?php

namespace App\Exports;

use App\Models\AyakManual;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class AyakManualExport implements FromCollection, WithHeadings
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
            'Jumlah Batok',
            'Jumlah Batok Mentah',
            'Jumlah Granul',
            'Keterangan'
        ];
    }
}
