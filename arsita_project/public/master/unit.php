<?php
ob_start(); // Prevent headers already sent
// public/master/unit.php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../includes/functions.php';
wajib_role(['admin_rektorat']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_unit'])) {
    $nama_unit = trim($_POST['nama_unit'] ?? '');
    $kode_unit = trim($_POST['kode_unit'] ?? '');
    $level = $_POST['level'] ?? 'prodi';
    $id_parent = ! empty($_POST['id_parent']) ? (int) $_POST['id_parent'] : null;

    if (empty($nama_unit) || empty($kode_unit)) {
        $error = 'Nama unit dan kode unit wajib diisi!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO units (nama_unit, kode_unit, level, id_parent, status) VALUES (?, ?, ?, ?, 'aktif')");
        $stmt->execute([$nama_unit, $kode_unit, $level, $id_parent]);
        set_flash('success', 'Unit kampus baru berhasil ditambahkan!');
        header('Location: unit.php');
        exit;
    }
}

$page_title = 'Master Unit Kampus';
require_once __DIR__.'/../../includes/header.php';

$units = $pdo->query('
    SELECT u.*, p.nama_unit as parent_nama
    FROM units u
    LEFT JOIN units p ON u.id_parent = p.id_unit
    ORDER BY u.level ASC, u.nama_unit ASC
')->fetchAll();

$parents_available = $pdo->query("SELECT * FROM units WHERE level IN ('rektorat', 'fakultas') AND status = 'aktif'")->fetchAll();
?>

<div class="detail-grid">
    <!-- Daftar Unit -->
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">Daftar Struktur Unit Kampus</div>
                <div class="card-subtitle">
                    Hierarki 3 Level Unit: Rektorat, Fakultas, dan Program Studi
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Unit</th>
                        <th>Level</th>
                        <th>Unit Induk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($units as $u) { ?>
                        <tr>
                            <td><b style="color: var(--primary);"><?= htmlspecialchars($u['kode_unit']) ?></b></td>
                            <td><b><?= htmlspecialchars($u['nama_unit']) ?></b></td>
                            <td>
                                <span style="text-transform: uppercase; font-size: 11px; font-weight: 700; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 4px;">
                                    <?= htmlspecialchars($u['level']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['parent_nama'] ?: '-') ?></td>
                            <td><span style="color: var(--success); font-weight: 600; font-size: 12px;">Aktif</span></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tambah Unit Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Tambah Unit Baru</div>
        </div>

        <?php if ($error) { ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label for="nama_unit">Nama Unit Kampus *</label>
                <input type="text" name="nama_unit" id="nama_unit" placeholder="Misal: Fakultas Syariah" required>
            </div>

            <div class="form-group">
                <label for="kode_unit">Kode Unit (Untuk Format Nomor) *</label>
                <input type="text" name="kode_unit" id="kode_unit" placeholder="Misal: Fak.02" required>
            </div>

            <div class="form-group">
                <label for="level">Level Hierarki *</label>
                <select name="level" id="level" required>
                    <option value="rektorat">Rektorat</option>
                    <option value="fakultas">Fakultas</option>
                    <option value="prodi">Program Studi (Prodi)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="id_parent">Unit Induk (Parent)</label>
                <select name="id_parent" id="id_parent">
                    <option value="">-- Tanpa Unit Induk (Top Level) --</option>
                    <?php foreach ($parents_available as $p) { ?>
                        <option value="<?= $p['id_unit'] ?>"><?= htmlspecialchars($p['nama_unit']) ?> (<?= htmlspecialchars($p['kode_unit']) ?>)</option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" name="tambah_unit" value="1" class="btn btn-primary" style="width: 100%;">
                Simpan Unit Kampus
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__.'/../../includes/footer.php'; ?>
