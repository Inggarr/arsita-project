<?php

namespace App\Exports;

use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class SuratMasukSheet implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function __construct(
        protected $listMasuk,
        protected string $startDate,
        protected string $endDate
    ) {}

    public function title(): string
    {
        return 'Surat Masuk';
    }

    public function collection(): Enumerable
    {
        return $this->listMasuk->map(function ($sm, $i) {
            return [
                'No' => $i + 1,
                'No. Surat Asal' => $sm->no_surat_asal,
                'Dari' => $sm->asal_surat,
                'Perihal' => $sm->perihal,
                'Tanggal Terima' => $sm->tanggal_terima,
                'Kategori' => $sm->kategori?->nama_kategori ?? 'Umum',
                'Unit' => $sm->unit?->nama_unit ?? '-',
                'Status' => $sm->status_arsip,
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'No. Surat Asal', 'Dari', 'Perihal', 'Tanggal Terima', 'Kategori', 'Unit', 'Status'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalData = $this->listMasuk->count();
                $dataLastRow = $totalData + 3;

                // Insert 2 rows at the top for title & subtitle
                $sheet->insertNewRowBefore(1, 2);

                // Row 1 — Main title
                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'LAPORAN SURAT MASUK');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1D4ED8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // Row 2 — Subtitle
                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', 'Periode: '.date('d/m/Y', strtotime($this->startDate)).'  s/d  '.date('d/m/Y', strtotime($this->endDate)).'     |     Dicetak: '.date('d/m/Y H:i'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Row 3 — Column headers
                $sheet->getStyle('A3:H3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93C5FD']]],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(20);

                // Data rows
                if ($totalData > 0) {
                    $sheet->getStyle("A4:H{$dataLastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);

                    for ($r = 4; $r <= $dataLastRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(18);

                        if ($r % 2 === 0) {
                            $sheet->getStyle("A{$r}:H{$r}")->applyFromArray([
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFF6FF']],
                            ]);
                        }
                    }

                    // No column center align
                    $sheet->getStyle("A4:A{$dataLastRow}")->applyFromArray([
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'font' => ['color' => ['argb' => 'FF94A3B8']],
                    ]);
                }

                // Set page layout for print (A4)
                $sheet->getPageSetup()
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToPage(true)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);

                $sheet->getPageMargins()->setTop(0.7)->setRight(0.5)->setBottom(0.7)->setLeft(0.5);
            },
        ];
    }
}
