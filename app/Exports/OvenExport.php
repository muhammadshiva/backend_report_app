<?php

namespace App\Exports;

use App\Models\Oven;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;


class OvenExport implements FromCollection, WithHeadings
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
            'Jenis Masukan',
            'Tanggal',
            'Sumber Batok',
            'Jenis Briket',
            'Pengovenan',
            'Pendinginan',
            'Keterangan'
        ];
    }
}
