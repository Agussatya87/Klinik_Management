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

// Only show patients who have at least one tindakan
$patients = fetchAll("SELECT DISTINCT p.idpasien, p.nama FROM pasien p JOIN tindakan t ON p.idpasien = t.idpasien ORDER BY p.nama");
// Only fetch rooms with status 'Kosong'
$rooms = fetchAll("SELECT idruang, nama_ruang FROM ruang WHERE status = 'Kosong' ORDER BY nama_ruang");
$tindakans = fetchAll("SELECT idtindakan, tindakan FROM tindakan ORDER BY tindakan");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpasien = (int)($_POST['idpasien'] ?? 0);
    $iddokter = (int)($_POST['iddokter'] ?? 0);
    $idruang = isset($_POST['idruang']) ? (int)$_POST['idruang'] : null;
    $diagnosis = sanitizeInput($_POST['diagnosis'] ?? '');
    $idtindakan = isset($_POST['idtindakan']) ? (int)$_POST['idtindakan'] : null;

    $errors = validateRequired($_POST, ['idpasien', 'iddokter', 'diagnosis']);

    if (empty($errors)) {
        $sql = "INSERT INTO rekam_medis (idpasien, iddokter, idruang, diagnosis, idtindakan) VALUES (?, ?, ?, ?, ?)";
        executeQuery($sql, [$idpasien, $iddokter, $idruang, $diagnosis, $idtindakan]);
        
        // Update room status if a room is assigned
        if ($idruang) {
            updateRoomStatus($idruang, 'Terisi');
        }
        
        setFlashMessage('success', 'Data rekam medis berhasil ditambahkan');
        header('Location: /Klinik_Management/index.php?page=rekam_medis');
        exit();
    }
}

// Fetch all doctors for fallback/manual selection
$doctors = fetchAll("SELECT iddokter, nama FROM dokter ORDER BY nama");

// Get next medical record ID for display
$nextId = fetchOne("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'db_klinik_management' AND TABLE_NAME = 'rekam_medis'");
$nextIdFormatted = 'RM' . str_pad($nextId['AUTO_INCREMENT'], 3, '0', STR_PAD_LEFT);

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Rekam Medis</h5>
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
                            <label class="form-label" for="idrm">ID Rekam Medis</label>
                            <input type="text" class="form-control" id="idrm" name="idrm" value="<?php echo $nextIdFormatted; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pasien *</label>
                            <select class="form-select" name="idpasien" id="idpasien" required>
                                <option value="">Pilih Pasien</option>
                                <?php foreach (
                                    $patients as $p): ?>
                                    <option value="<?= $p['idpasien'] ?>" <?= (($_POST['idpasien'] ?? '') == $p['idpasien']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tindakan</label>
                            <select class="form-select" name="idtindakan" id="idtindakan" disabled>
                                <option value="">Pilih Tindakan</option>
                                <?php foreach ($tindakans as $t): ?>
                                    <option value="<?= $t['idtindakan'] ?>" <?= (($_POST['idtindakan'] ?? '') == $t['idtindakan']) ? 'selected' : '' ?>><?= htmlspecialchars($t['tindakan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokter *</label>
                            <select class="form-select" name="iddokter" id="iddokter" required disabled>
                                <option value="">Pilih Dokter</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?= $d['iddokter'] ?>" <?= (($_POST['iddokter'] ?? '') == $d['iddokter']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ruang</label>
                            <select class="form-select" name="idruang">
                                <option value="">Pilih Ruang</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['idruang'] ?>" <?= (($_POST['idruang'] ?? '') == $r['idruang']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_ruang']) ?> (Tersedia)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Ruang yang tampil merupakan ruangan dengan status kosong</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis *</label>
                            <textarea class="form-control" name="diagnosis" required><?= htmlspecialchars($_POST['diagnosis'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=rekam_medis" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Disable dokter and tindakan fields by default
window.addEventListener('DOMContentLoaded', function() {
    document.getElementById('iddokter').disabled = true;
    document.getElementById('idtindakan').disabled = true;
});

document.getElementById('idpasien').addEventListener('change', function() {
    var idpasien = this.value;
    var dokterField = document.getElementById('iddokter');
    var tindakanField = document.getElementById('idtindakan');
    if (!idpasien) {
        dokterField.disabled = true;
        tindakanField.disabled = true;
        dokterField.value = '';
        tindakanField.value = '';
        return;
    }
    fetch('/Klinik_Management/pages/rekam_medis_autofill.php?idpasien=' + idpasien)
        .then(response => response.json())
        .then(data => {
            if (data.idtindakan) {
                tindakanField.value = data.idtindakan;
            } else {
                tindakanField.value = '';
            }
            if (data.iddokter) {
                dokterField.value = data.iddokter;
            } else {
                dokterField.value = '';
            }
            dokterField.disabled = false;
            tindakanField.disabled = false;
        });
});
</script>