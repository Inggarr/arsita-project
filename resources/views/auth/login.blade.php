<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login ARSITA — Sistem Persuratan dan Disposisi UINSSC">
    <title>Login — ARSITA</title>
    <!-- Bootstrap Icons (Lokal) -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
    <!-- ARSITA Design System -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Halaman Login Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <!-- Panel Kiri: Intro & Demo Akun -->
        <div class="intro-panel">
            <div class="intro-heading">
                <h2>ARSITA</h2>
                <p>Arsip Surat Instansi Terintegrasi Antarunit</p>
                <p class="intro-sub">UIN Siber Syekh Nurjati Cirebon</p>
            </div>

            <div class="demo-box">
                <h4><i class="bi bi-lightning-charge-fill"></i> Akun Uji Coba Cepat</h4>
                <p>Password semua: <strong>password123</strong> · Klik untuk isi otomatis</p>

                <div class="demo-groups" style="max-height: 480px; overflow-y: auto; padding-right: 4px;">

                    <!-- Unit Rektorat -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-building"></i> Rektorat &amp; Pimpinan Utama
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('admin_rektorat')" title="Admin Global">Admin Rektorat</li>
                            <li onclick="isi('staff_rektorat')" title="Staf TU Pusat">Staff TU Rektorat</li>
                            <li onclick="isi('rektor')" title="Rektor (R)">Rektor (R)</li>
                            <li onclick="isi('warek1')" title="Wakil Rektor I (R.I)">Warek I (R.I)</li>
                            <li onclick="isi('warek2')" title="Wakil Rektor II (R.II)">Warek II (R.II)</li>
                            <li onclick="isi('warek3')" title="Wakil Rektor III (R.III)">Warek III (R.III)</li>
                            <li onclick="isi('kabag')" title="Kepala Bagian">Kabag (Pimpinan)</li>
                            <li onclick="isi('kasubbag')" title="Kasubbag TU">Kasubbag (Pimpinan)</li>
                        </ul>
                    </div>

                    <!-- Biro AUAK, Senat & Dewan Penyantun -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-diagram-3-fill"></i> Biro AUAK, Senat &amp; Dewan
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('kepala_biro')" title="Kepala Biro (B)">Kepala Biro (B)</li>
                            <li onclick="isi('kabag_umum_akademik')" title="Kabag Umum &amp; Akademik (B.I)">Kabag Umum/Akademik (B.I)</li>
                            <li onclick="isi('tu_biro')" title="Staff TU Biro (JF.B)">TU Biro (JF.B)</li>
                            <li onclick="isi('ketua_senat')" title="Ketua Senat (S)">Ketua Senat (S)</li>
                            <li onclick="isi('staff_senat')" title="Staff Senat">Staff Senat</li>
                            <li onclick="isi('ketua_dewan_penyantun')" title="Ketua Dewan Penyantun (DP)">Ketua Dewan Penyantun (DP)</li>
                            <li onclick="isi('staff_dp')" title="Staff Dewan Penyantun">Staff DP</li>
                        </ul>
                    </div>

                    <!-- 1. Fakultas FITK (16 Prodi) -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-mortarboard"></i> 1. FITK (16 Prodi &amp; Wadek)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('dekan')" title="Dekan FITK (F.I)">Dekan FITK (F.I)</li>
                            <li onclick="isi('wadek1_fitk')" title="Wakil Dekan I FITK (F.I.1)">Wadek I FITK (F.I.1)</li>
                            <li onclick="isi('wadek2_fitk')" title="Wakil Dekan II FITK (F.I.2)">Wadek II FITK (F.I.2)</li>
                            <li onclick="isi('wadek3_fitk')" title="Wakil Dekan III FITK (F.I.3)">Wadek III FITK (F.I.3)</li>
                            <li onclick="isi('kalab_mipa')" title="Kepala Lab MIPA FITK (LB.I.1)">Lab MIPA (LB.I.1)</li>
                            <li onclick="isi('kalab_ibadah')" title="Kepala Lab Ibadah (LB.I.2)">Lab Ibadah (LB.I.2)</li>
                            <li onclick="isi('staff_fakultas')" title="Staf TU FITK">Staff TU FITK</li>
                            <li onclick="isi('kajur')" title="Kaprodi Informatika (J.I.16)">Kaprodi Informatika</li>
                            <li onclick="isi('staff_prodi')" title="TU Informatika">TU Informatika</li>
                            <li onclick="isi('kaprodi_mpi')" title="Kaprodi MPI (J.I.9)">Kaprodi MPI</li>
                            <li onclick="isi('tu_mpi')" title="TU MPI">TU MPI</li>
                            <li onclick="isi('kaprodi_mat')" title="Kaprodi Matematika">Kaprodi Matematika</li>
                            <li onclick="isi('tu_mat')" title="TU Matematika">TU Matematika</li>
                            <li onclick="isi('kaprodi_pai')" title="Kaprodi PAI">Kaprodi PAI</li>
                            <li onclick="isi('tu_pai')" title="TU PAI">TU PAI</li>
                            <li onclick="isi('kaprodi_pba')" title="Kaprodi PBA">Kaprodi PBA</li>
                            <li onclick="isi('tu_pba')" title="TU PBA">TU PBA</li>
                            <li onclick="isi('kaprodi_pgmi')" title="Kaprodi PGMI">Kaprodi PGMI</li>
                            <li onclick="isi('tu_pgmi')" title="TU PGMI">TU PGMI</li>
                            <li onclick="isi('kaprodi_piaud')" title="Kaprodi PIAUD">Kaprodi PIAUD</li>
                            <li onclick="isi('tu_piaud')" title="TU PIAUD">TU PIAUD</li>
                            <li onclick="isi('kaprodi_pjj_pai')" title="Kaprodi PJJ PAI">Kaprodi PJJ PAI</li>
                            <li onclick="isi('tu_pjj_pai')" title="TU PJJ PAI">TU PJJ PAI</li>
                            <li onclick="isi('kaprodi_pjj_pba')" title="Kaprodi PJJ PBA">Kaprodi PJJ PBA</li>
                            <li onclick="isi('tu_pjj_pba')" title="TU PJJ PBA">TU PJJ PBA</li>
                            <li onclick="isi('kaprodi_pjj_pgmi')" title="Kaprodi PJJ PGMI">Kaprodi PJJ PGMI</li>
                            <li onclick="isi('tu_pjj_pgmi')" title="TU PJJ PGMI">TU PJJ PGMI</li>
                            <li onclick="isi('kaprodi_tbin')" title="Kaprodi Tadris B. Indonesia">Kaprodi Tadris B. Indonesia</li>
                            <li onclick="isi('tu_tbin')" title="TU Tadris B. Indonesia">TU Tadris B. Indonesia</li>
                            <li onclick="isi('kaprodi_tbing')" title="Kaprodi Tadris B. Inggris">Kaprodi Tadris B. Inggris</li>
                            <li onclick="isi('tu_tbing')" title="TU Tadris B. Inggris">TU Tadris B. Inggris</li>
                            <li onclick="isi('kaprodi_tbio')" title="Kaprodi Tadris Biologi">Kaprodi Tadris Biologi</li>
                            <li onclick="isi('tu_tbio')" title="TU Tadris Biologi">TU Tadris Biologi</li>
                            <li onclick="isi('kaprodi_tips')" title="Kaprodi Tadris IPS">Kaprodi Tadris IPS</li>
                            <li onclick="isi('tu_tips')" title="TU Tadris IPS">TU Tadris IPS</li>
                            <li onclick="isi('kaprodi_tkim')" title="Kaprodi Tadris Kimia">Kaprodi Tadris Kimia</li>
                            <li onclick="isi('tu_tkim')" title="TU Tadris Kimia">TU Tadris Kimia</li>
                            <li onclick="isi('kaprodi_tmat')" title="Kaprodi Tadris Matematika">Kaprodi Tadris Matematika</li>
                            <li onclick="isi('tu_tmat')" title="TU Tadris Matematika">TU Tadris Matematika</li>
                        </ul>
                    </div>

                    <!-- 2. Fakultas FEBI (5 Prodi) -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-graph-up-arrow"></i> 2. FEBI (5 Prodi &amp; Wadek)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('dekan_febi')" title="Dekan FEBI (F.II)">Dekan FEBI (F.II)</li>
                            <li onclick="isi('wadek1_febi')" title="Wadek I FEBI (F.II.1)">Wadek I FEBI (F.II.1)</li>
                            <li onclick="isi('wadek2_febi')" title="Wadek II FEBI (F.II.2)">Wadek II FEBI (F.II.2)</li>
                            <li onclick="isi('kalab_febi')" title="Kepala Lab FEBI (LB.II.1)">Lab FEBI (LB.II.1)</li>
                            <li onclick="isi('staff_febi')" title="Staf TU FEBI">Staff TU FEBI</li>
                            <li onclick="isi('kaprodi_aks')" title="Kaprodi Akuntansi Syariah (J.II.3)">Kaprodi Akuntansi Syariah</li>
                            <li onclick="isi('tu_aks')" title="TU Akuntansi Syariah">TU Akuntansi Syariah</li>
                            <li onclick="isi('kaprodi_bio')" title="Kaprodi Bioteknologi">Kaprodi Bioteknologi</li>
                            <li onclick="isi('tu_bio')" title="TU Bioteknologi">TU Bioteknologi</li>
                            <li onclick="isi('kaprodi_eks')" title="Kaprodi Ekonomi Syariah (J.II.2)">Kaprodi Ekonomi Syariah</li>
                            <li onclick="isi('tu_eks')" title="TU Ekonomi Syariah">TU Ekonomi Syariah</li>
                            <li onclick="isi('kaprodi_par')" title="Kaprodi Pariwisata Syariah (J.II.4)">Kaprodi Pariwisata Syariah</li>
                            <li onclick="isi('tu_par')" title="TU Pariwisata Syariah">TU Pariwisata Syariah</li>
                            <li onclick="isi('kaprodi_pbs')" title="Kaprodi Perbankan Syariah (J.II.1)">Kaprodi Perbankan Syariah</li>
                            <li onclick="isi('tu_pbs')" title="TU Perbankan Syariah">TU Perbankan Syariah</li>
                        </ul>
                    </div>

                    <!-- 3. Fakultas Syariah (5 Prodi) -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-bank"></i> 3. Fakultas Syariah (FASYA)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('dekan_syariah')" title="Dekan Syariah (F.IV)">Dekan Syariah (F.IV)</li>
                            <li onclick="isi('wadek1_fasya')" title="Wadek I FASYA (F.IV.1)">Wadek I FASYA (F.IV.1)</li>
                            <li onclick="isi('wadek2_fasya')" title="Wadek II FASYA (F.IV.2)">Wadek II FASYA (F.IV.2)</li>
                            <li onclick="isi('kalab_fasya')" title="Kepala Lab Peradilan Agama (LB.IV.1)">Lab Peradilan (LB.IV.1)</li>
                            <li onclick="isi('staff_syariah')" title="Staf TU Syariah">Staff TU Syariah</li>
                            <li onclick="isi('kaprodi_hes')" title="Kaprodi Hukum Ekonomi Syariah (J.IV.2)">Kaprodi HES</li>
                            <li onclick="isi('tu_hes')" title="TU Hukum Ekonomi Syariah">TU HES</li>
                            <li onclick="isi('kaprodi_hk')" title="Kaprodi Hukum Keluarga (J.IV.1)">Kaprodi Hukum Keluarga</li>
                            <li onclick="isi('tu_hk')" title="TU Hukum Keluarga">TU Hukum Keluarga</li>
                            <li onclick="isi('kaprodi_htn')" title="Kaprodi Hukum Tatanegara Islam (J.IV.3)">Kaprodi HTN</li>
                            <li onclick="isi('tu_htn')" title="TU Hukum Tatanegara Islam">TU HTN</li>
                            <li onclick="isi('kaprodi_falak')" title="Kaprodi Ilmu Falak (J.IV.4)">Kaprodi Ilmu Falak</li>
                            <li onclick="isi('tu_falak')" title="TU Ilmu Falak">TU Ilmu Falak</li>
                            <li onclick="isi('kaprodi_pjj_hk')" title="Kaprodi PJJ Hukum Keluarga Islam (J.IV.5)">Kaprodi PJJ HK</li>
                            <li onclick="isi('tu_pjj_hk')" title="TU PJJ Hukum Keluarga Islam">TU PJJ HK</li>
                        </ul>
                    </div>

                    <!-- 4. Fakultas FDKI (4 Prodi) -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-megaphone"></i> 4. FDKI (4 Prodi &amp; Wadek)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('dekan_fdki')" title="Dekan FDKI (F.III)">Dekan FDKI (F.III)</li>
                            <li onclick="isi('wadek1_fdki')" title="Wadek I FDKI (F.III.1)">Wadek I FDKI (F.III.1)</li>
                            <li onclick="isi('wadek2_fdki')" title="Wadek II FDKI (F.III.2)">Wadek II FDKI (F.III.2)</li>
                            <li onclick="isi('kalab_kpi')" title="Kepala Lab KPI (LB.III.1)">Lab KPI (LB.III.1)</li>
                            <li onclick="isi('staff_fdki')" title="Staf TU FDKI">Staff TU FDKI</li>
                            <li onclick="isi('kaprodi_bki')" title="Kaprodi BKI (J.III.3)">Kaprodi BKI</li>
                            <li onclick="isi('tu_bki')" title="TU BKI">TU BKI</li>
                            <li onclick="isi('kaprodi_kpi')" title="Kaprodi KPI (J.III.1)">Kaprodi KPI</li>
                            <li onclick="isi('tu_kpi')" title="TU KPI">TU KPI</li>
                            <li onclick="isi('kaprodi_pmi')" title="Kaprodi PMI (J.III.2)">Kaprodi PMI</li>
                            <li onclick="isi('tu_pmi')" title="TU PMI">TU PMI</li>
                            <li onclick="isi('kaprodi_sa')" title="Kaprodi Sosiologi Agama (J.III.4)">Kaprodi Sosiologi Agama</li>
                            <li onclick="isi('tu_sa')" title="TU Sosiologi Agama">TU Sosiologi Agama</li>
                        </ul>
                    </div>

                    <!-- 5. Fakultas FUA (7 Prodi) -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-book-half"></i> 5. FUA (7 Prodi &amp; Wadek)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('dekan_fua')" title="Dekan FUA (F.V)">Dekan FUA (F.V)</li>
                            <li onclick="isi('wadek1_fua')" title="Wadek I FUA (F.V.1)">Wadek I FUA (F.V.1)</li>
                            <li onclick="isi('wadek2_fua')" title="Wadek II FUA (F.V.2)">Wadek II FUA (F.V.2)</li>
                            <li onclick="isi('kalab_fua')" title="Kepala Lab Tafsir Hadits (LB.V.1)">Lab Hadits (LB.V.1)</li>
                            <li onclick="isi('staff_fua')" title="Staf TU FUA">Staff TU FUA</li>
                            <li onclick="isi('kaprodi_afi')" title="Kaprodi Aqidah &amp; Filsafat Islam (J.V.2)">Kaprodi AFI</li>
                            <li onclick="isi('tu_afi')" title="TU Aqidah &amp; Filsafat Islam">TU AFI</li>
                            <li onclick="isi('kaprodi_bsa')" title="Kaprodi Bahasa &amp; Sastra Arab (J.V.5)">Kaprodi BSA</li>
                            <li onclick="isi('tu_bsa')" title="TU Bahasa &amp; Sastra Arab">TU BSA</li>
                            <li onclick="isi('kaprodi_iat')" title="Kaprodi IAT (J.V.3)">Kaprodi IAT</li>
                            <li onclick="isi('tu_iat')" title="TU IAT">TU IAT</li>
                            <li onclick="isi('kaprodi_ilha')" title="Kaprodi Ilmu Hadis (J.V.4)">Kaprodi Ilmu Hadis</li>
                            <li onclick="isi('tu_ilha')" title="TU Ilmu Hadis">TU Ilmu Hadis</li>
                            <li onclick="isi('kaprodi_pjj_spi')" title="Kaprodi PJJ SPI (J.V.7)">Kaprodi PJJ SPI</li>
                            <li onclick="isi('tu_pjj_spi')" title="TU PJJ SPI">TU PJJ SPI</li>
                            <li onclick="isi('kaprodi_spi')" title="Kaprodi SPI (J.V.1)">Kaprodi SPI</li>
                            <li onclick="isi('tu_spi')" title="TU SPI">TU SPI</li>
                            <li onclick="isi('kaprodi_tp')" title="Kaprodi Tasawuf &amp; Psikoterapi (J.V.6)">Kaprodi TP</li>
                            <li onclick="isi('tu_tp')" title="TU Tasawuf &amp; Psikoterapi">TU TP</li>
                        </ul>
                    </div>

                    <!-- 6. Lembaga -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-award"></i> Lembaga (LPM, LP2M, SPI)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('ketua_lpm')" title="Ketua LPM">Ketua LPM (Pimpinan)</li>
                            <li onclick="isi('sekretaris_lpm')" title="Sekretaris LPM">Sekretaris LPM (TU)</li>
                            <li onclick="isi('ketua_lp2m')" title="Ketua LP2M">Ketua LP2M (Pimpinan)</li>
                            <li onclick="isi('sekretaris_lp2m')" title="Sekretaris LP2M">Sekretaris LP2M (TU)</li>
                            <li onclick="isi('kepala_spi')" title="Kepala SPI">Kepala SPI (Pimpinan)</li>
                            <li onclick="isi('sekretaris_spi')" title="Sekretaris SPI">Sekretaris SPI (TU)</li>
                        </ul>
                    </div>

                    <!-- 7. UPT (Unit Pelaksana Teknis) -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-gear-wide-connected"></i> Unit Pelaksana Teknis (UPT)
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('kepala_upt_bahasa')" title="Kepala UPT Bahasa">Kepala UPT Bahasa (Pimpinan)</li>
                            <li onclick="isi('sekretaris_upt_bahasa')" title="Sekretaris UPT Bahasa">Sekretaris UPT Bahasa (TU)</li>
                            <li onclick="isi('kepala_upt_tik')" title="Kepala UPT TIK">Kepala UPT TIK (Pimpinan)</li>
                            <li onclick="isi('sekretaris_upt_tik')" title="Sekretaris UPT TIK">Sekretaris UPT TIK (TU)</li>
                            <li onclick="isi('kepala_upt_karir')" title="Kepala UPT Karir">Kepala UPT Karir (Pimpinan)</li>
                            <li onclick="isi('sekretaris_upt_karir')" title="Sekretaris UPT Karir">Sekretaris UPT Karir (TU)</li>
                            <li onclick="isi('kepala_upt_perpus')" title="Kepala UPT Perpustakaan">Kepala UPT Perpus (Pimpinan)</li>
                            <li onclick="isi('sekretaris_upt_perpus')" title="Sekretaris UPT Perpustakaan">Sekretaris UPT Perpus (TU)</li>
                            <li onclick="isi('kepala_upt_mahad')" title="Kepala UPT Ma'had">Kepala UPT Ma'had (Pimpinan)</li>
                            <li onclick="isi('sekretaris_upt_mahad')" title="Sekretaris UPT Ma'had">Sekretaris UPT Ma'had (TU)</li>
                            <li onclick="isi('kepala_upt_pjj')" title="Kepala UPT PJJ">Kepala UPT PJJ (Pimpinan)</li>
                            <li onclick="isi('sekretaris_upt_pjj')" title="Sekretaris UPT PJJ">Sekretaris UPT PJJ (TU)</li>
                        </ul>
                    </div>

                    <!-- 8. Pusat dan Unit -->
                    <div class="demo-group">
                        <div class="demo-group-title">
                            <i class="bi bi-grid-3x3-gap"></i> Pusat &amp; Unit Khusus
                        </div>
                        <ul class="demo-accounts">
                            <li onclick="isi('kepala_mutu_digital')" title="Kepala Standar Mutu Digital">Kepala Standar Mutu Digital</li>
                            <li onclick="isi('staff_pusat_mutu_digital')" title="Staff Mutu Digital">Staff Mutu Digital</li>
                            <li onclick="isi('kepala_audit_mutu')" title="Kepala Audit &amp; Pengendalian Mutu">Kepala Audit &amp; Pengendalian Mutu</li>
                            <li onclick="isi('staff_pusat_audit_mutu')" title="Staff Audit Mutu">Staff Audit Mutu</li>
                            <li onclick="isi('kepala_inovasi_digital')" title="Kepala Inovasi Pembelajaran Digital">Kepala Inovasi Digital</li>
                            <li onclick="isi('sekretaris_inovasi_digital')" title="Sekretaris Inovasi Pembelajaran Digital">Sekretaris Inovasi Digital</li>
                            <li onclick="isi('kepala_pusat_bisnis')" title="Kepala Pusat Bisnis">Kepala Pusat Bisnis</li>
                            <li onclick="isi('sekretaris_pusat_bisnis')" title="Sekretaris Pusat Bisnis">Sekretaris Pusat Bisnis</li>
                            <li onclick="isi('ketua_lsp')" title="Ketua LSP">Ketua LSP</li>
                            <li onclick="isi('sekretaris_lsp')" title="Sekretaris LSP">Sekretaris LSP</li>
                            <li onclick="isi('kepala_admisi')" title="Kepala Admisi &amp; Promosi">Kepala Admisi &amp; Promosi</li>
                            <li onclick="isi('sekretaris_admisi')" title="Sekretaris Admisi">Sekretaris Admisi</li>
                            <li onclick="isi('kepala_plt')" title="Kepala Pusat Layanan Terpadu">Kepala PLT</li>
                            <li onclick="isi('sekretaris_plt')" title="Sekretaris PLT">Sekretaris PLT</li>
                            <li onclick="isi('kepala_sdgs')" title="Kepala Pengabdian Masyarakat &amp; SDG's">Kepala SDG's</li>
                            <li onclick="isi('staff_pusat_sdgs')" title="Staff SDG's">Staff SDG's</li>
                            <li onclick="isi('kepala_industri')" title="Kepala Litbang Industri">Kepala Litbang Industri</li>
                            <li onclick="isi('staff_pusat_industri')" title="Staff Litbang Industri">Staff Industri</li>
                            <li onclick="isi('kepala_psga')" title="Kepala Pusat Studi Gender, Anak &amp; Disabilitas">Kepala PSGA</li>
                            <li onclick="isi('staff_psga')" title="Staff PSGA">Staff PSGA</li>
                            <li onclick="isi('kepala_iop')" title="Kepala International Office &amp; Partnership">Kepala IOP</li>
                            <li onclick="isi('sekretaris_iop')" title="Sekretaris IOP">Sekretaris IOP</li>
                            <li onclick="isi('ketua_lph')" title="Ketua Lembaga Pemeriksa Halal">Ketua LPH</li>
                            <li onclick="isi('sekretaris_lph')" title="Sekretaris LPH">Sekretaris LPH</li>
                            <li onclick="isi('kepala_pkbh')" title="Kepala Konsultasi &amp; Bantuan Hukum">Kepala PKBH</li>
                            <li onclick="isi('staff_pkbh')" title="Staff PKBH">Staff PKBH</li>
                            <li onclick="isi('kepala_publikasi')" title="Kepala Publikasi &amp; Pemeringkatan">Kepala Publikasi</li>
                            <li onclick="isi('staff_pusat_publikasi')" title="Staff Publikasi">Staff Publikasi</li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>

        <!-- Panel Kanan: Form Login -->
        <div class="login-box">
            <div class="login-brand">
                <div class="login-brand-icon" style="background: transparent; box-shadow: none; padding: 2px;">
                    <img src="{{ asset('assets/img/logo.png') }}" alt="Logo ARSITA" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <div>
                    <div class="login-brand-title">Masuk ke Sistem</div>
                    <div class="login-brand-sub">Silakan gunakan kredensial akun Anda</div>
                </div>
            </div>

            @if($errors->any())
                <div class="login-error">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="login-form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" value="{{ old('username', 'admin_rektorat') }}" required autofocus>
                </div>
                <div class="login-form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" value="password123" required>
                </div>
                <button type="submit" class="login-submit-btn">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
                </button>
            </form>
        </div>
    </div>

    <script>
        function isi(username) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = 'password123';
            document.getElementById('username').focus();
        }
    </script>
</body>
</html>
