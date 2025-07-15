<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Klinik_Management/login.php');
    exit();
}

// Ambil semua pasien untuk dropdown
$patients = fetchAll("SELECT idpasien, nama FROM pasien ORDER BY nama");
// Ambil semua dokter untuk dropdown
$doctors = fetchAll("SELECT iddokter, nama FROM dokter ORDER BY nama");
// List fasilitas medis
$facility_options = [
    'Radiology',
    'Laboratory',
    'ICU',
    'Ambulance',
    'Pharmacy',
    'Operating Room'
];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpasien = (int)($_POST['idpasien'] ?? 0);
    $kriteria = sanitizeInput($_POST['kriteria'] ?? '');
    $tindakan = sanitizeInput($_POST['tindakan'] ?? '');
    $iddokter = (int)($_POST['iddokter'] ?? 0);
    $fasilitas = isset($_POST['fasilitas']) ? sanitizeInput($_POST['fasilitas']) : null;
    $keputusan_keluarga = sanitizeInput($_POST['keputusan_keluarga'] ?? '');

    $errors = validateRequired($_POST, ['idpasien', 'kriteria', 'tindakan', 'iddokter']);

    if (empty($errors)) {
        $sql = "INSERT INTO tindakan (idpasien, kriteria, tindakan, iddokter, fasilitas, keputusan_keluarga) VALUES (?, ?, ?, ?, ?, ?)";
        executeQuery($sql, [$idpasien, $kriteria, $tindakan, $iddokter, $fasilitas, $keputusan_keluarga]);
        setFlashMessage('success', 'Data tindakan berhasil ditambahkan');
        header('Location: /Klinik_Management/index.php?page=tindakan');
        exit();
    }
}

// Get next tindakan ID for display
$nextId = fetchOne("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'db_klinik_management' AND TABLE_NAME = 'tindakan'");
$nextIdFormatted = 'TM' . str_pad($nextId['AUTO_INCREMENT'], 3, '0', STR_PAD_LEFT);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Data Tindakan</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= $e ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="idtindakan">ID Tindakan</label>
                            <input type="text" class="form-control" id="idtindakan" name="idtindakan" value="<?php echo $nextIdFormatted; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pasien *</label>
                            <select class="form-select" name="idpasien" required>
                                <option value="">Pilih Pasien</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['idpasien'] ?>" <?= (($_POST['idpasien'] ?? '') == $p['idpasien']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kriteria *</label>
                            <textarea class="form-control" name="kriteria" required><?= htmlspecialchars($_POST['kriteria'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tindakan *</label>
                            <textarea class="form-control" name="tindakan" required><?= htmlspecialchars($_POST['tindakan'] ?? '') ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokter *</label>
                            <select class="form-select" name="iddokter" required>
                                <option value="">Pilih Dokter</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?= $d['iddokter'] ?>" <?= (($_POST['iddokter'] ?? '') == $d['iddokter']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fasilitas</label>
                            <select class="form-select" name="fasilitas">
                                <option value="">Pilih Fasilitas</option>
                                <?php foreach ($facility_options as $f): ?>
                                    <option value="<?= htmlspecialchars($f) ?>" <?= (($_POST['fasilitas'] ?? '') == $f) ? 'selected' : '' ?>><?= htmlspecialchars($f) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keputusan Keluarga</label>
                            <textarea class="form-control" name="keputusan_keluarga"><?= htmlspecialchars($_POST['keputusan_keluarga'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=tindakan" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>