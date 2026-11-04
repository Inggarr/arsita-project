<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Persetujuan Surat</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155; line-height: 1.6;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                
                {{-- Container Utama --}}
                <table width="100%" max-width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">
                    
                    {{-- Header Email --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%); padding: 26px 30px; text-align: left;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <div style="font-size: 20px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 8px;">
                                            ARSITA UINSSC
                                        </div>
                                        <div style="font-size: 12px; color: #bfdbfe; margin-top: 3px;">
                                            Sistem Informasi Persuratan &amp; Disposisi Elektronik
                                        </div>
                                    </td>
                                    <td align="right">
                                        <span style="background: rgba(255, 255, 255, 0.15); color: #ffffff; padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.25);">
                                            Pemberitahuan ACC
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
                                Yth. {{ $pimpinan->nama ?? $pimpinan->name }},
                            </p>
                            <p style="font-size: 13.5px; color: #475569; margin: 0 0 20px 0; line-height: 1.6;">
                                Anda menerima permohonan persetujuan naskah dinas (ACC Surat Keluar). Mohon periksa dan berikan persetujuan atau catatan revisi pada lembar surat berikut:
                            </p>

                            {{-- Card Rincian Surat --}}
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px 20px; margin-bottom: 25px;">
                                <table width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px;">
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; width: 130px; vertical-align: top;">Perihal Surat</td>
                                        <td style="color: #0f172a; font-weight: 700; padding: 5px 0; vertical-align: top;">
                                            {{ $surat->perihal }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Unit Pembuat</td>
                                        <td style="color: #0f172a; font-weight: 600; padding: 5px 0; vertical-align: top;">
                                            {{ $surat->unit?->nama_unit ?? 'UINSSC' }} ({{ $surat->unit?->kode_unit ?? 'Un.30' }})
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Tujuan Surat</td>
                                        <td style="color: #0f172a; padding: 5px 0; vertical-align: top;">
                                            {{ $surat->tujuan }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Klasifikasi Sifat</td>
                                        <td style="padding: 5px 0; vertical-align: top;">
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: 700; font-size: 11.5px; text-transform: uppercase; background: #e0f2fe; color: #0369a1;">
                                                {{ $surat->sifat ?: 'Biasa' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Pembuat Draft</td>
                                        <td style="color: #0f172a; padding: 5px 0; vertical-align: top;">
                                            {{ $surat->pembuat?->nama ?? $surat->pembuat?->name ?? 'Staf' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="color: #64748b; padding: 5px 0; vertical-align: top;">Tanggal Diajukan</td>
                                        <td style="color: #0f172a; padding: 5px 0; vertical-align: top;">
                                            {{ \App\Services\ArsitaService::tglIndo($surat->tanggal_draft) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            {{-- Tombol Call to Action --}}
                            @php
                                $accUrl = $acc 
                                    ? route('persetujuan.show', $acc->id_acc) 
                                    : route('persetujuan.index');
                            @endphp
                            <div style="text-align: center; margin: 30px 0 25px 0;">
                                <a href="{{ $accUrl }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #ffffff; text-decoration: none; padding: 13px 32px; border-radius: 8px; font-size: 14px; font-weight: 700; box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35); text-align: center;">
                                    Tinjau &amp; Setujui Surat (ACC) &rarr;
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #94a3b8; text-align: center; margin: 0 0 10px 0;">
                                Atau salin tautan berikut ke browser Anda:<br>
                                <a href="{{ $accUrl }}" style="color: #2563eb; word-break: break-all;">{{ $accUrl }}</a>
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

