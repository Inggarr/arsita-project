<?php
// public/penomoran/index.php
ob_start();
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';

wajib_login();
wajib_role(['admin_rektorat', 'staff_tu', 'admin_unit', 'pimpinan']);

$id_user = $_SESSION['id_user'] ?? 0;
$id_unit = $_SESSION['id_unit'] ?? 0;
$role = $_SESSION['role'] ?? '';
$is_admin = ($role === 'admin_rektorat');

// Filter unit
$selected_unit_id = $id_unit;
if ($is_admin && ! empty($_GET['unit_id'])) {
    $selected_unit_id = (int) $_GET['unit_id'];
}

$error = '';

// Handle Submit Set Nomor Terakhir / Format Nomor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_unit_id = $is_admin ? (int) ($_POST['id_unit'] ?? $selected_unit_id) : $id_unit;
    $id_jenis_surat = (int) ($_POST['id_jenis_surat'] ?? 0);
    $kategori_id = ! empty($_POST['kategori_id']) ? (int) $_POST['kategori_id'] : null;
    $nomor_terakhir = max(0, (int) ($_POST['nomor_terakhir'] ?? 0));
    $format_nomor = trim($_POST['format_nomor'] ?? '{nomor}/{kode_jenis}/{kode_unit}/{tahun}');

    if (! $target_unit_id || ! $id_jenis_surat) {
        $error = 'Unit dan jenis surat wajib dipilih!';
    } else {
        // Ambil info kode unit dan kode jenis untuk simulasi contoh
        $uKode = $pdo->query("SELECT kode_unit FROM units WHERE id_unit = $target_unit_id")->fetchColumn() ?: 'UN.14';
        $jKode = $pdo->query("SELECT kode_jenis FROM jenis_surat WHERE id_jenis_surat = $id_jenis_surat")->fetchColumn() ?: 'SRT';

        // Nomor berikutnya untuk simulasi
        $nextNomor = str_pad($nomor_terakhir + 1, 3, '0', STR_PAD_LEFT);
        $contoh = str_replace(
            ['{nomor}', '{kode_jenis}', '{kode_unit}', '{tahun}'],
            [$nextNomor, $jKode, $uKode, date('Y')],
            $format_nomor
        );

        // Cek apakah konfigurasi untuk unit + jenis + kategori ini sudah ada
        if ($kategori_id) {
            $cek = $pdo->prepare('SELECT id_format FROM format_nomor WHERE id_unit = ? AND id_jenis_surat = ? AND kategori_id = ?');
            $cek->execute([$target_unit_id, $id_jenis_surat, $kategori_id]);
        } else {
            $cek = $pdo->prepare('SELECT id_format FROM format_nomor WHERE id_unit = ? AND id_jenis_surat = ? AND (kategori_id IS NULL OR kategori_id = 0)');
            $cek->execute([$target_unit_id, $id_jenis_surat]);
        }
        $ada_id = $cek->fetchColumn();

        if ($ada_id) {
            $stmt = $pdo->prepare('
                UPDATE format_nomor 
                SET nomor_terakhir = ?, format_nomor = ?, contoh = ?, aktif = 1 
                WHERE id_format = ?
            ');
            $stmt->execute([$nomor_terakhir, $format_nomor, $contoh, $ada_id]);
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO format_nomor (id_unit, id_jenis_surat, kategori_id, format_nomor, contoh, nomor_terakhir, aktif)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ');
            $stmt->execute([$target_unit_id, $id_jenis_surat, $kategori_id, $format_nomor, $contoh, $nomor_terakhir]);
        }

        set_flash('success', 'Nomor terakhir berhasil disimpan! Surat berikutnya akan dimulai dari urutan ke-<b>'.($nomor_terakhir + 1)."</b> (Contoh: <code>$contoh</code>).");
        header('Location: index.php'.($is_admin && $target_unit_id ? '?unit_id='.$target_unit_id : ''));
        exit;
    }
}

$page_title = 'Pengaturan Penomoran Surat';
require_once __DIR__.'/../../includes/header.php';

// Ambil master
$units_list = $pdo->query("SELECT * FROM units WHERE status = 'aktif' ORDER BY level ASC, nama_unit ASC")->fetchAll();
$jenis_list = $pdo->query('SELECT * FROM jenis_surat ORDER BY nama_jenis ASC')->fetchAll();
$kategori_list = $pdo->query('SELECT * FROM kategori_surat ORDER BY nama_kategori ASC')->fetchAll();

// Query daftar aturan penomoran
$sqlFormat = '
    SELECT fn.*, u.nama_unit, u.kode_unit, js.nama_jenis, js.kode_jenis, ks.nama_kategori
    FROM format_nomor fn
    JOIN units u ON fn.id_unit = u.id_unit
    JOIN jenis_surat js ON fn.id_jenis_surat = js.id_jenis_surat
    LEFT JOIN kategori_surat ks ON fn.kategori_id = ks.id_kategori
';
if (! $is_admin) {
    $sqlFormat .= ' WHERE fn.id_unit = '.(int) $id_unit;
} elseif (! empty($selected_unit_id)) {
    $sqlFormat .= ' WHERE fn.id_unit = '.(int) $selected_unit_id;
}
$sqlFormat .= ' ORDER BY u.id_unit ASC, js.nama_jenis ASC, ks.nama_kategori ASC';
$formats = $pdo->query($sqlFormat)->fetchAll();
?>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div>
            <div class="card-title">
                <i class="bi bi-hash text-primary"></i> Pengaturan Penomoran Surat & Nomor Terakhir
            </div>
            <div class="card-subtitle">
                Atur nomor terakhir yang sudah dipakai sebelumnya agar nomor surat baru otomatis melanjutkan (+1).
            </div>
        </div>
        <?php if ($is_admin) { ?>
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="filter_unit" style="margin: 0; font-size: 13px; font-weight: 600;">Filter Unit:</label>
                <select id="filter_unit" onchange="location.href='index.php?unit_id='+this.value" style="padding: 6px 10px; font-size: 13px;">
                    <option value="">-- Semua Unit --</option>
                    <?php foreach ($units_list as $un) { ?>
                        <option value="<?= $un['id_unit'] ?>" <?= $selected_unit_id == $un['id_unit'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($un['nama_unit']) ?> (<?= htmlspecialchars($un['kode_unit']) ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
    </div>
</div>

<div class="detail-grid">
    <!-- Tabel Pengaturan Penomoran -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Daftar Penomoran Unit</div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <?php if ($is_admin) { ?><th>Unit Kerja</th><?php } ?>
                        <th>Jenis Surat</th>
                        <th>Kategori</th>
                        <th style="text-align: center;">Nomor Terakhir</th>
                        <th style="text-align: center;">Nomor Berikutnya</th>
                        <th>Pola Format</th>
                        <th style="text-align: center; width: 70px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($formats)) { ?>
                        <tr>
                            <td colspan="<?= $is_admin ? 7 : 6 ?>" class="text-center text-muted" style="padding: 32px;">
                                Belum ada nomor terakhir yang disetel untuk unit ini. Silakan atur melalui form di samping.
                            </td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($formats as $f) {
                            $nextNum = (int) $f['nomor_terakhir'] + 1;
                            ?>
                            <tr>
                                <?php if ($is_admin) { ?>
                                    <td><b><?= htmlspecialchars($f['nama_unit']) ?></b> <small class="text-muted">(<?= htmlspecialchars($f['kode_unit']) ?>)</small></td>
                                <?php } ?>
                                <td>
                                    <span class="badge badge-info" style="font-weight: 600;">
                                        <?= htmlspecialchars($f['kode_jenis']) ?>
                                    </span>
                                    <div style="font-size: 12px; margin-top: 2px;"><?= htmlspecialchars($f['nama_jenis']) ?></div>
                                </td>
                                <td>
                                    <?php if ($f['nama_kategori']) { ?>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($f['nama_kategori']) ?></span>
                                    <?php } else { ?>
                                        <span class="text-muted" style="font-size: 12px;">(Semua Kategori)</span>
                                    <?php } ?>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-size: 16px; font-weight: 700; color: var(--text-primary);">
                                        <?= (int) $f['nomor_terakhir'] ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success" style="font-size: 13px; font-weight: 700;">
                                        +1 &rarr; <?= $nextNum ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;"><?= htmlspecialchars($f['format_nomor']) ?></div>
                                    <div style="font-size: 12px; font-weight: 600; color: var(--primary); margin-top: 2px;"><?= htmlspecialchars($f['contoh']) ?></div>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-secondary btn-sm" 
                                            onclick="editPenomoran('<?= $f['id_unit'] ?>', '<?= $f['id_jenis_surat'] ?>', '<?= (int) $f['kategori_id'] ?>', '<?= (int) $f['nomor_terakhir'] ?>', '<?= htmlspecialchars(addslashes($f['format_nomor'])) ?>')"
                                            title="Ubah Nomor Terakhir">
                                        <i class="bi bi-pencil-square"></i> Set
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Input / Update Nomor Terakhir -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Set Nomor Terakhir</div>
                <div class="card-subtitle">Masukkan nomor terakhir dari buku agenda/manual</div>
            </div>
        </div>

        <?php if ($error) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; font-size: 12.5px; color: #166534;">
            <i class="bi bi-info-circle-fill"></i> <b>Cara Kerja Penomoran +1:</b><br>
            Jika Anda memasukkan nomor terakhir <b>25</b>, maka surat baru yang disetujui pimpinan berikutnya akan otomatis mendapatkan nomor <b>026</b>, lalu <b>027</b>, dan seterusnya.
        </div>

        <form method="POST">
            <?php if ($is_admin) { ?>
                <div class="form-group">
                    <label for="id_unit">Pilih Unit Kampus *</label>
                    <select name="id_unit" id="id_unit" required>
                        <option value="">-- Pilih Unit --</option>
                        <?php foreach ($units_list as $u) { ?>
                            <option value="<?= $u['id_unit'] ?>" <?= $selected_unit_id == $u['id_unit'] ? 'selected' : '' ?>>
                                [<?= strtoupper($u['level']) ?>] <?= htmlspecialchars($u['nama_unit']) ?> (<?= htmlspecialchars($u['kode_unit']) ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>

            <div class="form-group">
                <label for="id_jenis_surat">Jenis Surat *</label>
                <select name="id_jenis_surat" id="id_jenis_surat" required>
                    <option value="">-- Pilih Jenis Surat --</option>
                    <?php foreach ($jenis_list as $j) { ?>
                        <option value="<?= $j['id_jenis_surat'] ?>">
                            <?= htmlspecialchars($j['nama_jenis']) ?> (Kode: <?= htmlspecialchars($j['kode_jenis']) ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="kategori_id">Kategori Khusus (Opsional)</label>
                <select name="kategori_id" id="kategori_id">
                    <option value="">-- Berlaku untuk Semua Kategori --</option>
                    <?php foreach ($kategori_list as $k) { ?>
                        <option value="<?= $k['id_kategori'] ?>">
                            <?= htmlspecialchars($k['nama_kategori']) ?>
                        </option>
                    <?php } ?>
                </select>
                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 11.5px;">
                    Kosongkan jika jenis surat ini penomorannya digabung untuk semua urusan kategori.
                </small>
            </div>

            <div class="form-group">
                <label for="nomor_terakhir">Nomor Terakhir Saat Ini *</label>
                <input type="number" name="nomor_terakhir" id="nomor_terakhir" min="0" placeholder="Contoh: 105 (jika surat terakhir nomor 105)" required style="font-size: 16px; font-weight: 700; color: var(--primary);">
                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 11.5px;">
                    Surat berikutnya otomatis bernilai: <b><span id="preview_next">1</span></b>
                </small>
            </div>

            <div class="form-group">
                <label for="format_nomor">Pola Format Nomor (Template) *</label>
                <input type="text" name="format_nomor" id="format_nomor" value="{nomor}/{kode_jenis}/{kode_unit}/{tahun}" required>
                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 11px;">
                    Placeholder: <code>{nomor}</code>, <code>{kode_jenis}</code>, <code>{kode_unit}</code>, <code>{tahun}</code>
                </small>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                <i class="bi bi-save-fill"></i> Simpan Penomoran Terakhir
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('nomor_terakhir').addEventListener('input', function() {
    var val = parseInt(this.value) || 0;
    document.getElementById('preview_next').innerText = (val + 1);
});

function editPenomoran(idUnit, idJenis, idKat, nomorTerakhir, formatNomor) {
    var unitSel = document.getElementById('id_unit');
    if (unitSel) {
        unitSel.value = idUnit;
    }
    document.getElementById('id_jenis_surat').value = idJenis;
    document.getElementById('kategori_id').value = idKat > 0 ? idKat : '';
    document.getElementById('nomor_terakhir').value = nomorTerakhir;
    document.getElementById('preview_next').innerText = parseInt(nomorTerakhir) + 1;
    if (formatNomor) {
        document.getElementById('format_nomor').value = formatNomor;
    }
    document.getElementById('nomor_terakhir').focus();
    window.scrollTo({ top: document.querySelector('.card:last-child').offsetTop - 20, behavior: 'smooth' });
}
</script>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
