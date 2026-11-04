<?php

namespace Tests\Feature;

use App\Exports\LaporanExport;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Exporter;
use Tests\TestCase;

class LaporanExportTest extends TestCase
{
    public function test_laporan_export_implements_export_interface(): void
    {
        $export = new LaporanExport(collect(), collect(), '2026-01-01', '2026-10-24');

        $this->assertInstanceOf(Export::class, $export);
        $this->assertCount(2, $export->sheets());
    }

    public function test_laporan_export_can_generate_raw_binary(): void
    {
        $dummyMasuk = collect([
            (object) [
                'no_surat_asal' => '001/TEST/2026',
                'asal_surat' => 'Instansi Pengirim',
                'perihal' => 'Uji Coba Ekspor',
                'tanggal_terima' => '2026-05-01',
                'kategori' => (object) ['nama_kategori' => 'Dinas'],
                'unit' => (object) ['nama_unit' => 'Bagian Umum'],
                'status_arsip' => 'Selesai',
            ],
        ]);

        $dummyKeluar = collect([
            (object) [
                'nomorSurat' => (object) ['nomor_surat' => '002/OUT/2026'],
                'tujuan' => 'Instansi Tujuan',
                'perihal' => 'Jawaban Permohonan',
                'tanggal_draft' => '2026-05-02',
                'kategori' => (object) ['nama_kategori' => 'Dinas'],
                'unit' => (object) ['nama_unit' => 'Bagian Umum'],
                'status_surat' => 'Disetujui',
            ],
        ]);

        $export = new LaporanExport($dummyMasuk, $dummyKeluar, '2026-01-01', '2026-10-24');

        /** @var Exporter $excelService */
        $excelService = app(Exporter::class);
        $rawBinary = $excelService->raw($export, ExcelWriter::XLSX);

        $this->assertNotEmpty($rawBinary);
        $this->assertGreaterThan(1000, strlen($rawBinary));
    }
}
