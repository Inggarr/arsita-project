<?php
// public/master/format_nomor.php
header('Location: ../penomoran/index.php');
exit;

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_format'])) {
    $id_unit = (int) ($_POST['id_unit'] ?? 0);
    $id_jenis_surat = (int) ($_POST['id_jenis_surat'] ?? 0);
    $format_nomor = trim($_POST['format_nomor'] ?? '');

    if (! $id_unit || ! $id_jenis_surat || empty($format_nomor)) {
        $error = 'Unit, jenis surat, dan template format nomor wajib diisi!';
    } else {
        // Ambil info kode unit dan kode jenis untuk contoh
        $uKode = $pdo->query("SELECT kode_unit FROM units WHERE id_unit = $id_unit")->fetchColumn() ?: 'UN.14';
        $jKode = $pdo->query("SELECT kode_jenis FROM jenis_surat WHERE id_jenis_surat = $id_jenis_surat")->fetchColumn() ?: 'SRT';
        $contoh = str_replace(
            ['{nomor}', '{kode_jenis}', '{kode_unit}', '{tahun}'],
            ['001', $jKode, $uKode, date('Y')],
            $format_nomor
        );

        // Cek apakah sudah ada format untuk pasangan ini
        $cek = $pdo->prepare('SELECT id_format FROM format_nomor WHERE id_unit = ? AND id_jenis_surat = ?');
        $cek->execute([$id_unit, $id_jenis_surat]);
        $ada = $cek->fetchColumn();

        if ($ada) {
            $stmt = $pdo->prepare('UPDATE format_nomor SET format_nomor = ?, contoh = ? WHERE id_format = ?');
            $stmt->execute([$format_nomor, $contoh, $ada]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO format_nomor (id_unit, id_jenis_surat, format_nomor, contoh, aktif) VALUES (?, ?, ?, ?, 1)');
            $stmt->execute([$id_unit, $id_jenis_surat, $format_nomor, $contoh]);
        }

        set_flash('success', 'Format nomor otomatis berhasil diperbarui!');
        header('Location: format_nomor.php');
        exit;
    }
}

// Daftar format nomor yang ada
$formats = $pdo->query('
    SELECT fn.*, u.nama_unit, u.kode_unit, js.nama_jenis, js.kode_jenis
    FROM format_nomor fn
    JOIN units u ON fn.id_unit = u.id_unit
    JOIN jenis_surat js ON fn.id_jenis_surat = js.id_jenis_surat
    ORDER BY u.id_unit ASC, js.id_jenis_surat ASC
')->fetchAll();

$units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();
?>

<div class="detail-grid">
    <!-- Daftar Format Nomor -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Aturan Penomoran Surat Otomatis ARSITA</div>
                <div class="card-subtitle">
                    Logika pola format nomor per unit dan jenis surat di seluruh UINSSC
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Unit Kampus</th>
                        <th>Jenis Surat</th>
                        <th>Pola Template</th>
                        <th>Simulasi Contoh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($formats)) { ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted" style="padding:24px">Belum ada format yang diatur.</td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($formats as $f) { ?>
                            <tr>
                                <td><b><?= htmlspecialchars($f['nama_unit']) ?></b> (<?= htmlspecialchars($f['kode_unit']) ?>)</td>
                                <td><?= htmlspecialchars($f['nama_jenis']) ?> (<?= htmlspecialchars($f['kode_jenis']) ?>)</td>
                                <td><code><?= htmlspecialchars($f['format_nomor']) ?></code></td>
                                <td><b style="color: var(--primary);"><?= htmlspecialchars($f['contoh']) ?></b></td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Setting Format Baru -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Atur Format Nomor</div>
        </div>

        <?php if ($error) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 12px; font-size: 12px; margin-bottom: 16px;">
            <b>Variabel Placeholder yang Tersedia:</b><br>
            - <code>{nomor}</code> : Nomor urut (001, 002, dst)<br>
            - <code>{kode_jenis}</code> : Kode Jenis Surat (SK, ST, dll)<br>
            - <code>{kode_unit}</code> : Kode Unit (UN.14, Fak.01, dll)<br>
            - <code>{tahun}</code> : Tahun berjalan (<?= date('Y') ?>)
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="id_unit">Unit Kampus *</label>
                <select name="id_unit" id="id_unit" required>
                    <option value="">-- Pilih Unit --</option>
                    <?php foreach ($units_list as $u) { ?>
                        <option value="<?= $u['id_unit'] ?>"><?= htmlspecialchars($u['nama_unit']) ?> (<?= htmlspecialchars($u['kode_unit']) ?>)</option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_jenis_surat">Jenis Surat *</label>
                <select name="id_jenis_surat" id="id_jenis_surat" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    <?php foreach ($jenis_list as $j) { ?>
                        <option value="<?= $j['id_jenis_surat'] ?>"><?= htmlspecialchars($j['nama_jenis']) ?> (<?= htmlspecialchars($j['kode_jenis']) ?>)</option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="format_nomor">Template Pola Nomor *</label>
                <input type="text" name="format_nomor" id="format_nomor" value="{nomor}/{kode_jenis}/{kode_unit}/{tahun}" required>
            </div>

            <button type="submit" name="simpan_format" value="1" class="btn btn-primary" style="width: 100%;">
                Simpan Aturan Penomoran
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
