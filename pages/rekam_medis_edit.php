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

if (!isset($_GET['idrm']) || !is_numeric($_GET['idrm'])) {
    setFlashMessage('danger', 'ID rekam medis tidak valid');
    header('Location: /Klinik_Management/index.php?page=rekam_medis');
    exit();
}

$idrm = (int)$_GET['idrm'];
$rekam_medis = fetchOne("SELECT * FROM rekam_medis WHERE idrm = ?", [$idrm]);
if (!$rekam_medis) {
    setFlashMessage('danger', 'Data rekam medis tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=rekam_medis');
    exit();
}

$patients = fetchAll("SELECT idpasien, nama FROM pasien ORDER BY nama");
$rooms = getAllRooms(); // Get all rooms with status
$tindakans = fetchAll("SELECT idtindakan, tindakan FROM tindakan ORDER BY tindakan");
$doctors = fetchAll("SELECT iddokter, nama FROM dokter ORDER BY nama");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpasien = (int)($_POST['idpasien'] ?? 0);
    $iddokter = (int)($_POST['iddokter'] ?? 0);
    $idruang = isset($_POST['idruang']) ? (int)$_POST['idruang'] : null;
    $diagnosis = sanitizeInput($_POST['diagnosis'] ?? '');
    $idtindakan = isset($_POST['idtindakan']) ? (int)$_POST['idtindakan'] : null;

    $errors = validateRequired($_POST, ['idpasien', 'iddokter', 'diagnosis']);

    if (empty($errors)) {
        // Get the old room ID before updating
        $old_ruang_id = $rekam_medis['idruang'];
        
        $sql = "UPDATE rekam_medis SET idpasien = ?, iddokter = ?, idruang = ?, diagnosis = ?, idtindakan = ? WHERE idrm = ?";
        executeQuery($sql, [$idpasien, $iddokter, $idruang, $diagnosis, $idtindakan, $idrm]);
        
        // Update room status based on changes
        updateRoomStatusOnMedicalRecordChange($old_ruang_id, $idruang);
        
        setFlashMessage('success', 'Data rekam medis berhasil diperbarui');
        header('Location: /Klinik_Management/index.php?page=rekam_medis');
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Rekam Medis</h5>
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
                            <label class="form-label">Pasien *</label>
                            <select class="form-select" name="idpasien" id="idpasien" required>
                                <option value="">Pilih Pasien</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?= $p['idpasien'] ?>" <?= ((isset($_POST['idpasien']) ? $_POST['idpasien'] : $rekam_medis['idpasien']) == $p['idpasien']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tindakan</label>
                            <select class="form-select" name="idtindakan" id="idtindakan">
                                <option value="">Pilih Tindakan</option>
                                <?php foreach ($tindakans as $t): ?>
                                    <option value="<?= $t['idtindakan'] ?>" <?= ((isset($_POST['idtindakan']) ? $_POST['idtindakan'] : $rekam_medis['idtindakan']) == $t['idtindakan']) ? 'selected' : '' ?>><?= htmlspecialchars($t['tindakan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokter *</label>
                            <select class="form-select" name="iddokter" id="iddokter" required>
                                <option value="">Pilih Dokter</option>
                                <?php foreach ($doctors as $d): ?>
                                    <option value="<?= $d['iddokter'] ?>" <?= ((isset($_POST['iddokter']) ? $_POST['iddokter'] : $rekam_medis['iddokter']) == $d['iddokter']) ? 'selected' : '' ?>><?= htmlspecialchars($d['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ruang</label>
                            <select class="form-select" name="idruang">
                                <option value="">Pilih Ruang</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= $r['idruang'] ?>" <?= ((isset($_POST['idruang']) ? $_POST['idruang'] : $rekam_medis['idruang']) == $r['idruang']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($r['nama_ruang']) ?> 
                                        (<?= $r['status'] === 'Kosong' ? 'Tersedia' : 'Terisi' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Status ruang akan otomatis diperbarui berdasarkan perubahan</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis *</label>
                            <textarea class="form-control" name="diagnosis" required><?= htmlspecialchars(isset($_POST['diagnosis']) ? $_POST['diagnosis'] : $rekam_medis['diagnosis']) ?></textarea>
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
document.getElementById('idpasien').addEventListener('change', function() {
    var idpasien = this.value;
    if (!idpasien) return;
    fetch('/Klinik_Management/pages/rekam_medis_autofill.php?idpasien=' + idpasien)
        .then(response => response.json())
        .then(data => {
            if (data.idtindakan) {
                document.getElementById('idtindakan').value = data.idtindakan;
            }
            if (data.iddokter) {
                document.getElementById('iddokter').value = data.iddokter;
            }
        });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?> 