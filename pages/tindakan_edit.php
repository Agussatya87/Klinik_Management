<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /Klinik_Management/login.php');
    exit();
}

if (!isset($_GET['idtindakan']) || !is_numeric($_GET['idtindakan'])) {
    setFlashMessage('danger', 'ID tindakan tidak valid');
    header('Location: /Klinik_Management/index.php?page=tindakan');
    exit();
}

$idtindakan = (int)$_GET['idtindakan'];
$tindakan = fetchOne("SELECT * FROM tindakan WHERE idtindakan = ?", [$idtindakan]);
if (!$tindakan) {
    setFlashMessage('danger', 'Data tindakan tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=tindakan');
    exit();
}

$patients = fetchAll("SELECT idpasien, nama FROM pasien ORDER BY nama");
$doctors = fetchAll("SELECT iddokter, nama FROM dokter ORDER BY nama");

$facility_options = [
    'Radiology', 'Laboratory', 'ICU', 'Ambulance', 'Pharmacy', 'Operating Room'
];

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpasien = (int)($_POST['idpasien'] ?? 0);
    $kriteria = sanitizeInput($_POST['kriteria'] ?? '');
    $tindakan_val = sanitizeInput($_POST['tindakan'] ?? '');
    $iddokter = (int)($_POST['iddokter'] ?? 0);
    $fasilitas = isset($_POST['fasilitas']) ? sanitizeInput($_POST['fasilitas']) : null;
    $keputusan_keluarga = sanitizeInput($_POST['keputusan_keluarga'] ?? '');

    $errors = validateRequired($_POST, ['idpasien', 'kriteria', 'tindakan', 'iddokter']);

    if (empty($errors)) {
        $sql = "UPDATE tindakan SET idpasien=?, kriteria=?, tindakan=?, iddokter=?, fasilitas=?, keputusan_keluarga=? WHERE idtindakan=?";
        executeQuery($sql, [$idpasien, $kriteria, $tindakan_val, $iddokter, $fasilitas, $keputusan_keluarga, $idtindakan]);
        setFlashMessage('success', 'Data tindakan berhasil diperbarui');
        header('Location: /Klinik_Management/index.php?page=tindakan');
        exit();
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-pencil-square text-primary"></i> Edit Data Tindakan</h1>
        <a href="/Klinik_Management/index.php?page=tindakan" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label">Pasien <span class="text-danger">*</span></label>
                    <select class="form-select" name="idpasien" required>
                        <option value="">Pilih Pasien</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?= $p['idpasien'] ?>" <?= (($tindakan['idpasien'] == $p['idpasien']) ? 'selected' : '') ?>>
                                <?= htmlspecialchars($p['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kriteria <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="kriteria" required><?= htmlspecialchars($_POST['kriteria'] ?? $tindakan['kriteria']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tindakan <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="tindakan" required><?= htmlspecialchars($_POST['tindakan'] ?? $tindakan['tindakan']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dokter <span class="text-danger">*</span></label>
                    <select class="form-select" name="iddokter" required>
                        <option value="">Pilih Dokter</option>
                        <?php foreach ($doctors as $d): ?>
                            <option value="<?= $d['iddokter'] ?>" <?= (($tindakan['iddokter'] == $d['iddokter']) ? 'selected' : '') ?>>
                                <?= htmlspecialchars($d['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fasilitas</label>
                    <select class="form-select" name="fasilitas">
                        <option value="">Pilih Fasilitas</option>
                        <?php foreach ($facility_options as $f): ?>
                            <option value="<?= $f ?>" <?= (($tindakan['fasilitas'] == $f) ? 'selected' : '') ?>>
                                <?= htmlspecialchars($f) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keputusan Keluarga</label>
                    <textarea class="form-control" name="keputusan_keluarga"><?= htmlspecialchars($_POST['keputusan_keluarga'] ?? $tindakan['keputusan_keluarga']) ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Simpan Perubahan
                    </button>
                    <a href="/Klinik_Management/index.php?page=tindakan" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

