<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements Export, WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected $listMasuk,
        protected $listKeluar,
        protected string $startDate,
        protected string $endDate
    ) {}

    /** @return array<int, mixed> */
    public function sheets(): array
    {
        return [
            new SuratMasukSheet($this->listMasuk, $this->startDate, $this->endDate),
            new SuratKeluarSheet($this->listKeluar, $this->startDate, $this->endDate),
        ];
    }
}
