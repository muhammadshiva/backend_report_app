<?php

namespace App\Exports;

use App\Models\AyakRotari;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class AyakRotariExport implements FromCollection, WithHeadings
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
            'Batok Kotor',
            'Hasil Batok',
            'Hasil Abu',
            'Keterangan'
        ];
    }
}
