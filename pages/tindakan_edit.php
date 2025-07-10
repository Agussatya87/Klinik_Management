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

if (!isset($_GET['idtindakan']) || !is_numeric($_GET['idtindakan'])) {
    setFlashMessage('danger', 'ID tindakan tidak valid');
    header('Location: /Klinik_Management/pages/tindakan.php');
    exit();
}

$idtindakan = (int)$_GET['idtindakan'];
$tindakan = fetchOne("SELECT * FROM tindakan WHERE idtindakan = ?", [$idtindakan]);
if (!$tindakan) {
    setFlashMessage('danger', 'Data tindakan tidak ditemukan');
    header('Location: /Klinik_Management/pages/tindakan.php');
    exit();
}

$patients = fetchAll("SELECT idpasien, nama FROM pasien ORDER BY nama");

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idpasien = (int)$_POST['idpasien'];
    $kriteria = sanitizeInput($_POST['kriteria']);
    $tindakan_val = sanitizeInput($_POST['tindakan']);
    $dokter = sanitizeInput($_POST['dokter']);
    $fasilitas = sanitizeInput($_POST['fasilitas']);
    $keputusan_keluarga = sanitizeInput($_POST['keputusan_keluarga']);

    $errors = validateRequired($_POST, ['idpasien', 'kriteria', 'tindakan', 'dokter']);

    if (empty($errors)) {
        $sql = "UPDATE tindakan SET idpasien=?, kriteria=?, tindakan=?, dokter=?, fasilitas=?, keputusan_keluarga=? WHERE idtindakan=?";
        executeQuery($sql, [$idpasien, $kriteria, $tindakan_val, $dokter, $fasilitas, $keputusan_keluarga, $idtindakan]);
        setFlashMessage('success', 'Data tindakan berhasil diperbarui');
        header('Location: /Klinik_Management/pages/tindakan.php');
        exit();
    }
}
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Data Tindakan</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?php echo $e; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Pasien *</label>
                            <select class="form-select" name="idpasien" required>
                                <option value="">Pilih Pasien</option>
                                <?php foreach ($patients as $p): ?>
                                    <option value="<?php echo $p['idpasien']; ?>" <?php if((isset($_POST['idpasien']) ? $_POST['idpasien'] : $tindakan['idpasien'])==$p['idpasien']) echo 'selected'; ?>><?php echo htmlspecialchars($p['nama']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kriteria *</label>
                            <textarea class="form-control" name="kriteria" required><?php echo isset($_POST['kriteria']) ? htmlspecialchars($_POST['kriteria']) : htmlspecialchars($tindakan['kriteria']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tindakan *</label>
                            <textarea class="form-control" name="tindakan" required><?php echo isset($_POST['tindakan']) ? htmlspecialchars($_POST['tindakan']) : htmlspecialchars($tindakan['tindakan']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokter *</label>
                            <input type="text" class="form-control" name="dokter" required value="<?php echo isset($_POST['dokter']) ? htmlspecialchars($_POST['dokter']) : htmlspecialchars($tindakan['dokter']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fasilitas</label>
                            <textarea class="form-control" name="fasilitas"><?php echo isset($_POST['fasilitas']) ? htmlspecialchars($_POST['fasilitas']) : htmlspecialchars($tindakan['fasilitas']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keputusan Keluarga</label>
                            <textarea class="form-control" name="keputusan_keluarga"><?php echo isset($_POST['keputusan_keluarga']) ? htmlspecialchars($_POST['keputusan_keluarga']) : htmlspecialchars($tindakan['keputusan_keluarga']); ?></textarea>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/pages/tindakan.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 