<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Masuk Baru</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155; line-height: 1.6;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">

                {{-- Container Utama --}}
                <table width="100%" max-width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">

                    {{-- Header Email --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); padding: 26px 30px; text-align: left;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">
                                            ARSITA UINSSC
                                        </div>
                                        <div style="font-size: 12px; color: #99f6e4; margin-top: 3px;">
                                            Sistem Informasi Persuratan &amp; Disposisi Elektronik
                                        </div>
                                    </td>
                                    <td align="right">
                                        <span style="background: rgba(255, 255, 255, 0.15); color: #ffffff; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.25);">
                                            Surat Masuk Baru
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Isi Pesan --}}
                    <tr>
                        <td style="padding: 30px 30px 20px 30px;">
                            <p style="font-size: 15px; font-weight: 700; color: #0f172a; margin: 0 0 10px 0;">
                                Yth. {{ $penerima->nama ?? $penerima->name }},
                            </p>
                            <p style="font-size: 13.5px; color: #475569; margin: 0 0 20px 0; line-height: 1.6;">
                                Terdapat <strong>surat masuk baru</strong> yang masuk ke unit Anda dan perlu segera ditinjau. Mohon lakukan disposisi atau pengarsipan pada surat berikut:
                            </p>

                            {{-- Card Rincian Surat --}}
                            <div style="background-color: #f0fdfa; border: 1px solid #99f6e4; border-radius: 10px; padding: 18px 20px; margin-bottom: 25px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; width: 130px; vertical-align: top;">Nomor Surat</td>
                                        <td style="color: #0f172a; font-weight: 700; padding: 5px 0; vertical-align: top;">
                                            {{ $suratMasuk->no_surat_asal }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Perihal</td>
                                        <td style="color: #0f172a; font-weight: 700; padding: 5px 0; vertical-align: top;">
                                            {{ $suratMasuk->perihal }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Asal Surat</td>
                                        <td style="color: #0f172a; padding: 5px 0; vertical-align: top;">
                                            {{ $suratMasuk->asal_surat }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Tanggal Surat</td>
                                        <td style="color: #0f172a; padding: 5px 0; vertical-align: top;">
                                            {{ \App\Services\ArsitaService::tglIndo($suratMasuk->tanggal_surat) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Tanggal Terima</td>
                                        <td style="color: #0f172a; padding: 5px 0; vertical-align: top;">
                                            {{ \App\Services\ArsitaService::tglIndo($suratMasuk->tanggal_terima) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Unit Penerima</td>
                                        <td style="color: #0f172a; font-weight: 600; padding: 5px 0; vertical-align: top;">
                                            {{ $suratMasuk->unit?->nama_unit ?? '-' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Tombol Call to Action --}}
                            @php
                                $suratUrl = route('surat-masuk.show', $suratMasuk->id_surat_masuk);
                            @endphp
                            <div style="text-align: center; margin: 30px 0 25px 0;">
                                <a href="{{ $suratUrl }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: #ffffff; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 12px rgba(15, 118, 110, 0.35); text-align: center;">
                                    Tinjau Surat Masuk &rarr;
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #94a3b8; text-align: center; margin: 0 0 10px 0;">
                                Atau salin tautan berikut ke browser Anda:<br>
                                <a href="{{ $suratUrl }}" style="color: #0d9488; word-break: break-all;">{{ $suratUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Footer Email --}}
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;">
                            <p style="font-size: 11.5px; color: #64748b; margin: 0 0 4px 0;">
                                Email ini dikirim otomatis oleh <strong>Sistem ARSITA</strong> (Universitas Islam Negeri Sunan Syekh Nurjati Cirebon).
                            </p>
                            <p style="font-size: 11px; color: #94a3b8; margin: 0;">
                                Harap tidak membalas email ini secara langsung.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>

