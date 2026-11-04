@php
    $kop = $kop ?? \App\Models\PengaturanSurat::getKop();
    $subUnit = $subUnit ?? ($kop['sub_unit_default'] ?? null);

    $fontFamily = $kop['font_family'] ?? 'Arial, Helvetica, sans-serif';
    $garisKetebalan = $kop['garis_ketebalan'] ?? '3px';
    $garisTipe = $kop['garis_tipe'] ?? 'double';
    $garisWarna = $kop['garis_warna'] ?? '#000000';

    $kemenagFontSize = $kop['kemenag_font_size'] ?? '14pt';
    $kemenagBold = !empty($kop['kemenag_bold']);
    $kemenagColor = $kop['kemenag_color'] ?? '#000000';

    $instansiFontSize = $kop['instansi_font_size'] ?? '12pt';
    $instansiBold = !empty($kop['instansi_bold']);
    $instansiColor = $kop['instansi_color'] ?? '#000000';

    $subunitFontSize = $kop['subunit_font_size'] ?? '11pt';
    $subunitBold = !empty($kop['subunit_bold']);
    $subunitColor = $kop['subunit_color'] ?? '#000000';

    $kontakFontSize = $kop['kontak_font_size'] ?? '9pt';
    $kontakItalic = !empty($kop['kontak_italic']);
    $kontakColor = $kop['kontak_color'] ?? '#000000';
@endphp

<table class="arsita-kop-surat" style="width: 100%; border-collapse: collapse; border-bottom: {{ $garisKetebalan }} {{ $garisTipe }} {{ $garisWarna }}; margin-bottom: 18px; font-family: {{ $fontFamily }};">
    <tr>
        <td style="width: 110px; vertical-align: middle; text-align: center; padding-right: 18px; padding-bottom: 10px;">
            @if(!empty($kop['logo_instansi']))
                <img src="{{ asset('uploads/pengaturan/' . $kop['logo_instansi']) }}" class="kop-logo-img" style="width: 100px; max-height: 105px; object-fit: contain; display: inline-block;" alt="Logo Instansi">
            @else
                <div style="width: 90px; height: 90px;"></div>
            @endif
        </td>
        <td style="vertical-align: middle; text-align: center; padding-bottom: 10px;">
            <div style="font-size: {{ $kemenagFontSize }}; font-weight: {{ $kemenagBold ? 'bold' : 'normal' }}; text-transform: uppercase; letter-spacing: 0.5px; color: {{ $kemenagColor }}; line-height: 1.25; margin-bottom: 2px;">
                {{ $kop['nama_kementerian'] ?? 'KEMENTERIAN AGAMA REPUBLIK INDONESIA' }}
            </div>
            <div style="font-size: {{ $instansiFontSize }}; font-weight: {{ $instansiBold ? 'bold' : 'normal' }}; text-transform: uppercase; color: {{ $instansiColor }}; line-height: 1.25; margin-bottom: 2px;">
                {{ $kop['nama_instansi'] ?? 'UNIVERSITAS ISLAM NEGERI SIBER SYEKH NURJATI CIREBON' }}
            </div>
            @if(!empty($subUnit))
                <div id="{{ $subUnitId ?? 'pv_unit' }}" style="font-size: {{ $subunitFontSize }}; font-weight: {{ $subunitBold ? 'bold' : 'normal' }}; text-transform: uppercase; color: {{ $subunitColor }}; line-height: 1.25; margin-bottom: 2px;">
                    {{ $subUnit }}
                </div>
            @else
                <div id="{{ $subUnitId ?? 'pv_unit' }}" style="font-size: {{ $subunitFontSize }}; font-weight: {{ $subunitBold ? 'bold' : 'normal' }}; text-transform: uppercase; color: {{ $subunitColor }}; line-height: 1.25; margin-bottom: 2px; display: none;"></div>
            @endif
            <div style="font-size: {{ $kontakFontSize }}; color: {{ $kontakColor }}; font-style: {{ $kontakItalic ? 'italic' : 'normal' }}; line-height: 1.35; margin-top: 3px;">
                {{ $kop['alamat_instansi'] ?? '' }}@if(!empty($kop['kota'])) {{ $kop['kota'] }}@endif @if(!empty($kop['kode_pos'])) {{ $kop['kode_pos'] }}@endif;
                <br>
                @if(!empty($kop['telepon_instansi'])) Telepon {{ $kop['telepon_instansi'] }} @endif
                @if(!empty($kop['website_instansi'])) <em>Website:</em> {{ $kop['website_instansi'] }} @endif
                @if(!empty($kop['email_instansi'])) &bull; <em>Email:</em> {{ $kop['email_instansi'] }} @endif
            </div>
        </td>
    </tr>
</table>
