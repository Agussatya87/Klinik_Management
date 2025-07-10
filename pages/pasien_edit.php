<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_GET['idpasien']) || !is_numeric($_GET['idpasien'])) {
    setFlashMessage('danger', 'ID pasien tidak valid');
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

$idpasien = (int)$_GET['idpasien'];
$sql = "SELECT * FROM pasien WHERE idpasien = ?";
$patient = fetchOne($sql, [$idpasien]);

if (!$patient) {
    setFlashMessage('danger', 'Data pasien tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitizeInput($_POST['nama']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $pekerjaan = sanitizeInput($_POST['pekerjaan']);
    $tmp_lahir = sanitizeInput($_POST['tmp_lahir']);
    $tgl_lahir = $_POST['tgl_lahir'];
    $telpon = sanitizeInput($_POST['telpon']);
    $alamat = sanitizeInput($_POST['alamat']);
    $tgl_daftar = $_POST['tgl_daftar'] ?? date('Y-m-d');

    $errors = validateRequired($_POST, ['nama', 'jenis_kelamin', 'telpon']);

    if (empty($errors)) {
        $sql = "UPDATE pasien SET nama = ?, jenis_kelamin = ?, pekerjaan = ?, tmp_lahir = ?, tgl_lahir = ?, telpon = ?, alamat = ?, tgl_daftar = ? WHERE idpasien = ?";
        executeQuery($sql, [$nama, $jenis_kelamin, $pekerjaan, $tmp_lahir, $tgl_lahir, $telpon, $alamat, $tgl_daftar, $idpasien]);
        setFlashMessage('success', 'Data pasien berhasil diperbarui');
        header('Location: /Klinik_Management/index.php?page=pasien');
        exit();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-pencil-square text-primary"></i> Edit Data Pasien
        </h1>
        <a href="/Klinik_Management/index.php?page=pasien" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo $e; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($patient['nama']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" name="jenis_kelamin" required>
                            <option value="L" <?php if($patient['jenis_kelamin']=='L') echo 'selected'; ?>>Laki-laki</option>
                            <option value="P" <?php if($patient['jenis_kelamin']=='P') echo 'selected'; ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Pekerjaan</label>
                        <input type="text" class="form-control" name="pekerjaan" value="<?php echo htmlspecialchars($patient['pekerjaan']); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" name="tmp_lahir" value="<?php echo htmlspecialchars($patient['tmp_lahir']); ?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tgl_lahir" value="<?php echo $patient['tgl_lahir']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="telpon" value="<?php echo htmlspecialchars($patient['telpon']); ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <input type="text" class="form-control" name="alamat" value="<?php echo htmlspecialchars($patient['alamat']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Daftar</label>
                    <input type="date" class="form-control" name="tgl_daftar" value="<?php echo $patient['tgl_daftar']; ?>">
                </div>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan Perubahan</button>
                <a href="/Klinik_Management/index.php?page=pasien" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 