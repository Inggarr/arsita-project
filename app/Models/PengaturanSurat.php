<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSurat extends Model
{
    protected $table = 'pengaturan_surat';

    protected $fillable = ['key', 'value', 'group'];

    /**
     * Ambil satu nilai setting berdasarkan key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Simpan / update nilai setting.
     */
    public static function set(string $key, mixed $value, string $group = 'umum'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );
    }

    /**
     * Ambil semua setting dalam satu group sebagai key-value array.
     *
     * @return array<string, string|null>
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Ambil pengaturan Kop Surat dengan fallback & alias lengkap
     *
     * @return array<string, string|null>
     */
    public static function getKop(): array
    {
        $raw = static::group('kop_surat');

        $kementerian = $raw['kop_kementerian'] ?? $raw['nama_kementerian'] ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA';
        $namaInstansi = $raw['kop_nama_instansi'] ?? $raw['nama_instansi'] ?? 'UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON';
        $subUnitDefault = $raw['kop_sub_unit'] ?? $raw['sub_unit'] ?? '';
        $singkatan = $raw['kop_singkatan'] ?? $raw['singkatan'] ?? 'UINSSC';
        $alamat = $raw['kop_alamat'] ?? $raw['alamat_instansi'] ?? 'Jalan Perjuangan By Pass Sunyaragi';
        $kota = $raw['kop_kota'] ?? $raw['kota'] ?? 'Cirebon';
        $kodePos = $raw['kop_kode_pos'] ?? $raw['kode_pos'] ?? '45132';
        $telepon = $raw['kop_telepon'] ?? $raw['telepon_instansi'] ?? '(0231) 481264';
        $email = $raw['kop_email'] ?? $raw['email_instansi'] ?? 'info@uinssc.ac.id';
        $website = $raw['kop_website'] ?? $raw['website_instansi'] ?? 'www.uinssc.ac.id';
        $logo = $raw['kop_logo'] ?? $raw['logo_instansi'] ?? null;

        // Atribut Tipografi & Gaya Kop Kemenag Sesuai Standar Resmi (Default Arial)
        $fontFamily = $raw['kop_font_family'] ?? 'Arial, Helvetica, sans-serif';
        $kemenagFontSize = $raw['kop_kemenag_font_size'] ?? '14pt';
        $kemenagBold = (bool) ($raw['kop_kemenag_bold'] ?? true);
        $kemenagColor = $raw['kop_kemenag_color'] ?? '#000000';

        $instansiFontSize = $raw['kop_instansi_font_size'] ?? '12pt';
        $instansiBold = (bool) ($raw['kop_instansi_bold'] ?? true);
        $instansiColor = $raw['kop_instansi_color'] ?? '#000000';

        $subunitFontSize = $raw['kop_subunit_font_size'] ?? '11pt';
        $subunitBold = (bool) ($raw['kop_subunit_bold'] ?? true);
        $subunitColor = $raw['kop_subunit_color'] ?? '#000000';

        $kontakFontSize = $raw['kop_kontak_font_size'] ?? '9pt';
        $kontakItalic = (bool) ($raw['kop_kontak_italic'] ?? false);
        $kontakColor = $raw['kop_kontak_color'] ?? '#000000';

        $garisKetebalan = $raw['kop_garis_ketebalan'] ?? '3px';
        $garisTipe = $raw['kop_garis_tipe'] ?? 'double';
        $garisWarna = $raw['kop_garis_warna'] ?? '#000000';

        return [
            // Standard normalized keys
            'nama_kementerian' => $kementerian,
            'nama_instansi' => $namaInstansi,
            'sub_unit_default' => $subUnitDefault,
            'singkatan' => $singkatan,
            'alamat_instansi' => $alamat,
            'kota' => $kota,
            'kode_pos' => $kodePos,
            'telepon_instansi' => $telepon,
            'email_instansi' => $email,
            'website_instansi' => $website,
            'logo_instansi' => $logo,

            // Tipografi & Styling
            'font_family' => $fontFamily,
            'kemenag_font_size' => $kemenagFontSize,
            'kemenag_bold' => $kemenagBold,
            'kemenag_color' => $kemenagColor,

            'instansi_font_size' => $instansiFontSize,
            'instansi_bold' => $instansiBold,
            'instansi_color' => $instansiColor,

            'subunit_font_size' => $subunitFontSize,
            'subunit_bold' => $subunitBold,
            'subunit_color' => $subunitColor,

            'kontak_font_size' => $kontakFontSize,
            'kontak_italic' => $kontakItalic,
            'kontak_color' => $kontakColor,

            'garis_ketebalan' => $garisKetebalan,
            'garis_tipe' => $garisTipe,
            'garis_warna' => $garisWarna,

            // Raw form aliases
            'kop_kementerian' => $kementerian,
            'kop_nama_instansi' => $namaInstansi,
            'kop_sub_unit' => $subUnitDefault,
            'kop_singkatan' => $singkatan,
            'kop_alamat' => $alamat,
            'kop_kota' => $kota,
            'kop_kode_pos' => $kodePos,
            'kop_telepon' => $telepon,
            'kop_email' => $email,
            'kop_website' => $website,
            'kop_logo' => $logo,
            'kop_font_family' => $fontFamily,
            'kop_kemenag_font_size' => $kemenagFontSize,
            'kop_kemenag_bold' => $kemenagBold,
            'kop_kemenag_color' => $kemenagColor,
            'kop_instansi_font_size' => $instansiFontSize,
            'kop_instansi_bold' => $instansiBold,
            'kop_instansi_color' => $instansiColor,
            'kop_subunit_font_size' => $subunitFontSize,
            'kop_subunit_bold' => $subunitBold,
            'kop_subunit_color' => $subunitColor,
            'kop_kontak_font_size' => $kontakFontSize,
            'kop_kontak_italic' => $kontakItalic,
            'kop_kontak_color' => $kontakColor,
            'kop_garis_ketebalan' => $garisKetebalan,
            'kop_garis_tipe' => $garisTipe,
            'kop_garis_warna' => $garisWarna,
        ];
    }

    /**
     * Ambil pengaturan Format Dokumen/Surat dengan fallback & konversi satuan
     *
     * @return array<string, mixed>
     */
    public static function getFormat(): array
    {
        $raw = static::group('format_surat');

        $kertas = $raw['format_ukuran_kertas'] ?? $raw['ukuran_kertas'] ?? 'A4';
        $font = $raw['format_font'] ?? $raw['jenis_font'] ?? "'Times New Roman', Times, serif";
        $ukuranFont = (int) ($raw['format_ukuran_font'] ?? $raw['ukuran_font'] ?? 12);
        $spasi = (float) ($raw['format_spasi'] ?? $raw['spasi_baris'] ?? 1.5);

        // Nilai margin di input form disimpan dalam cm (misal 3, 4), konversi ke mm jika <= 10
        $rawAtas = (float) ($raw['format_margin_atas'] ?? $raw['margin_atas'] ?? 25.4);
        $rawKanan = (float) ($raw['format_margin_kanan'] ?? $raw['margin_kanan'] ?? 25.4);
        $rawBawah = (float) ($raw['format_margin_bawah'] ?? $raw['margin_bawah'] ?? 25.4);
        $rawKiri = (float) ($raw['format_margin_kiri'] ?? $raw['margin_kiri'] ?? 25.4);

        $marginAtas = $rawAtas <= 10 ? $rawAtas * 10 : $rawAtas;
        $marginKanan = $rawKanan <= 10 ? $rawKanan * 10 : $rawKanan;
        $marginBawah = $rawBawah <= 10 ? $rawBawah * 10 : $rawBawah;
        $marginKiri = $rawKiri <= 10 ? $rawKiri * 10 : $rawKiri;

        return [
            'ukuran_kertas' => $kertas,
            'jenis_font' => $font,
            'ukuran_font' => $ukuranFont,
            'spasi_baris' => $spasi,
            'margin_atas' => $marginAtas,
            'margin_kanan' => $marginKanan,
            'margin_bawah' => $marginBawah,
            'margin_kiri' => $marginKiri,

            // Raw form aliases
            'format_ukuran_kertas' => $kertas,
            'format_font' => $font,
            'format_ukuran_font' => $ukuranFont,
            'format_spasi' => $spasi,
            'format_margin_atas' => $rawAtas,
            'format_margin_kanan' => $rawKanan,
            'format_margin_bawah' => $rawBawah,
            'format_margin_kiri' => $rawKiri,
        ];
    }
}
