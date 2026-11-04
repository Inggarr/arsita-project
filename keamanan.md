# 🛡️ Dokumentasi Keamanan Sistem ARSITA UIN Siber Cirebon (UINSSC)

Dokumen ini mencatat seluruh implementasi dan penguatan keamanan (*security hardening*) yang telah diterapkan pada sistem persuratan dan disposisi elektronik ARSITA.

---

## 1. 🛡️ Proteksi Brute-Force Login (Rate Limiting & Throttling)
* **File Target**: `app/Http/Controllers/AuthController.php`
* **Celah Sebelumnya**: Percobaan menebak username/password dapat dilakukan tanpa batas waktu oleh bot/attacker (*credential stuffing* & *dictionary attack*).
* **Solusi & Implementasi**:
  * Menggunakan `Illuminate\Support\Facades\RateLimiter` berbasis kombinasi `Str::lower($username) . '|' . $request->ip()`.
  * Dibatasi maksimal **5 kali percobaan gagal**.
  * Jika kuota terlewati, endpoint login langsung dikunci selama **60 detik** dan memberikan pesan waktu tunggu hitung mundur kepada user.
  * Ketika login berhasil, counter percobaan gagal langsung dibersihkan (`RateLimiter::clear`).

---

## 2. 🛡️ Proteksi Hak Akses Endpoint Berbasis Role (Middleware Authorization)
* **File Target**:
  * `app/Http/Middleware/EnsureUserRole.php` (Middleware Baru)
  * `bootstrap/app.php` (Pendaftaran alias `role`)
  * `routes/web.php`
* **Celah Sebelumnya**: Seluruh route hanya dilindungi middleware `auth` standar, sehingga staf biasa berpotensi mengakses route sensitif seperti manajemen pengguna atau persetujuan surat hanya dengan menebak URL.
* **Solusi & Implementasi**:
  * **Persetujuan / ACC Surat Keluar** (`/persetujuan/*`): Khusus untuk role `pimpinan` dan `admin_rektorat`.
  * **Manajemen Pengguna** (`/pengguna/*`): Khusus untuk role `admin_rektorat`. Mencegah pembuatan admin/user palsu oleh staf biasa.
  * **Master Data Organisasi** (`/master/*`): Khusus untuk role `admin_rektorat`.
  * **Pengaturan Sistem** (`/pengaturan/*`): Khusus untuk `admin_rektorat`, `admin_unit`, dan `staff_tu`.
  * Jika diakses oleh role yang tidak sah, sistem otomatis memicu response **`403 Forbidden`**.

---

## 3. 🛡️ Proteksi Data Surat Antar-Unit (Anti-IDOR / Policy Authorization)
* **File Target**:
  * `app/Policies/SuratKeluarPolicy.php` (Policy Baru)
  * `app/Policies/SuratMasukPolicy.php` (Policy Baru)
  * `app/Http/Controllers/SuratKeluarController.php`
  * `app/Http/Controllers/SuratMasukController.php`
* **Celah Sebelumnya**: Kerentanan *Insecure Direct Object Reference* (IDOR), di mana staf di Unit A bisa melihat atau mengedit surat rahasia milik Unit B hanya dengan memanipulasi parameter ID pada URL (`/surat-keluar/15/edit`).
* **Solusi & Implementasi**:
  * Memasang pengecekan `$this->authorize('view', $surat)` dan `$this->authorize('update', $surat)` di level controller.
  * **Surat Keluar**: Hanya dapat dilihat dan diedit oleh staf unit pembuat surat atau unit penerima internal.
  * **Surat Masuk**: Hanya dapat dibuka oleh unit penerima. Surat masuk hasil disposisi atau surat antar-unit tidak dapat diedit/dihapus sembarangan oleh penerima.
  * `admin_rektorat` memiliki wewenang pengawasan menyeluruh (`before` hook).

---

## 4. 🛡️ Otorisasi Lembar & Tindak Lanjut Disposisi
* **File Target**: `app/Http/Controllers/DisposisiController.php`
* **Celah Sebelumnya**: Detail lembar disposisi (`/disposisi/{id}`) dan aksi tindak lanjutnya dapat diakses siapapun yang mengetahui nomor ID disposisi.
* **Solusi & Implementasi**:
  * Pada method `show()` dan `tindakLanjut()`, ditambahkan verifikasi kepemilikan ketat:
    * Harus merupakan pegawai penerima disposisi individu (`ke_user_id`), atau
    * Anggota unit penerima disposisi unit (`ke_unit_id`), atau
    * Pengirim lembar disposisi (`dari_user_id`), atau
    * Admin Rektorat.
  * Akses di luar pihak berwenang langsung diblokir dengan pesan **`403 Forbidden`**.

---

## 5. 🛡️ HTTP Security Headers (Browser Hardening)
* **File Target**:
  * `app/Http/Middleware/SecurityHeadersMiddleware.php` (Middleware Global Baru)
  * `bootstrap/app.php`
* **Celah Sebelumnya**: Respons HTTP belum menyertakan header proteksi bawaan browser, membuka peluang serangan *Clickjacking* dan *MIME-type sniffing*.
* **Solusi & Implementasi**:
  Setiap HTTP response dari aplikasi ARSITA otomatis disisipkan header keamanan berikut:
  1. `X-Frame-Options: SAMEORIGIN` — Melindungi web agar tidak bisa di-embed di iframe situs luar (anti-Clickjacking).
  2. `X-Content-Type-Options: nosniff` — Mencegah browser mengeksekusi lampiran berkas sebagai script palsu.
  3. `Referrer-Policy: strict-origin-when-cross-origin` — Mencegah URL sensitif bocor ke pihak luar saat redirect.
  4. `X-XSS-Protection: 1; mode=block` — Perlindungan ekstra terhadap serangan Cross-Site Scripting.
  5. `Permissions-Policy: geolocation=(), microphone=(), camera=()` — Menonaktifkan akses sensor/perangkat keras yang tidak digunakan aplikasi persuratan.

---

## 6. 🛡️ Standar Kekuatan Password (Password Policy)
* **File Target**:
  * `app/Http/Controllers/PenggunaController.php`
  * `app/Http/Controllers/ProfilController.php`
* **Celah Sebelumnya**: Pembuatan akun pengguna dan ubah password sebelumnya hanya mensyaratkan minimal 6 karakter bebas tanpa standar keamanan.
* **Solusi & Implementasi**:
  * Menerapkan aturan `Illuminate\Validation\Rules\Password::min(8)->letters()->numbers()`.
  * Password baru wajib memiliki panjang minimal **8 karakter** serta wajib memadukan **huruf dan angka**, mengurangi risiko serangan tebakan kata sandi lemah.

---

## 7. 🛡️ Sanitasi Ekstensi & Validasi File Upload (Anti Remote Code Execution / MIME-Spoofing)
* **File Target**:
  * `app/Http/Controllers/SuratKeluarController.php`
  * `app/Http/Controllers/SuratMasukController.php`
  * `app/Http/Controllers/DisposisiController.php`
* **Celah Sebelumnya**: Penamaan file menggunakan `$file->getClientOriginalExtension()` yang rawan manipulasi ekstensi dari sisi client (contoh nama file ganda: `script.php.png` atau memalsukan ekstensi executable).
* **Solusi & Implementasi**:
  * Mengganti pengambilan ekstensi ke `$file->extension()`, di mana ekstensi ditentukan oleh analisis MIME-type asli dari server Laravel/PHP, bukan nama buatan client.
  * Tetap membatasi ukuran maksimal (5MB) dan jenis dokumen yang diizinkan (`pdf`, `doc`, `docx`, `jpg`, `png`).

---

## 8. 🛡️ Otorisasi Cetak Surat Keluar & Pembatasan Rekap Laporan Ekspor
* **File Target**:
  * `app/Http/Controllers/SuratKeluarController.php` (method `cetak()`)
  * `routes/web.php`
* **Celah Sebelumnya**:
  * Halaman cetak surat (`/surat-keluar/{id}/cetak`) sebelumnya dapat diakses oleh siapa saja yang login hanya dengan menebak ID surat.
  * Endpoint ekspor laporan PDF/Excel (`/laporan/*`) terbuka bebas untuk seluruh staf.
* **Solusi & Implementasi**:
  * Method `cetak()` kini dilindungi `$this->authorize('view', $surat)` sehingga hanya unit pembuat, unit tujuan, atau rektorat yang bisa mencetak.
  * Route ekspor laporan (`/laporan`, `/laporan/pdf`, `/laporan/excel`) dikunci khusus untuk role berwenang: `pimpinan`, `admin_rektorat`, dan `admin_unit`.

---

*Terakhir diperbarui: 04 November 2026*

