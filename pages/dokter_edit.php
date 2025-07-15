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

if (!isset($_GET['iddokter']) || !is_numeric($_GET['iddokter'])) {
    setFlashMessage('danger', 'ID dokter tidak valid');
    header('Location: /Klinik_Management/index.php?page=dokter');
    exit();
}

$iddokter = (int)$_GET['iddokter'];
$dokter = fetchOne("SELECT * FROM dokter WHERE iddokter = ?", [$iddokter]);
if (!$dokter) {
    setFlashMessage('danger', 'Data dokter tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=dokter');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitizeInput($_POST['nama']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $telpon = sanitizeInput($_POST['telpon']);
    $alamat = sanitizeInput($_POST['alamat']);
    $spesialisasi = sanitizeInput($_POST['spesialisasi']);

    $errors = validateRequired($_POST, ['nama', 'jenis_kelamin', 'telpon', 'spesialisasi']);

    if (empty($errors)) {
        $sql = "UPDATE dokter SET nama=?, jenis_kelamin=?, telpon=?, alamat=?, spesialisasi=? WHERE iddokter=?";
        executeQuery($sql, [$nama, $jenis_kelamin, $telpon, $alamat, $spesialisasi, $iddokter]);
        setFlashMessage('success', 'Data dokter berhasil diperbarui');
        header('Location: /Klinik_Management/index.php?page=dokter');
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
                    <h5 class="mb-0">Edit Data Dokter</h5>
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
                            <label class="form-label">Nama *</label>
                            <input type="text" class="form-control" name="nama" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : htmlspecialchars($dokter['nama']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis Kelamin *</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" <?php if((isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : $dokter['jenis_kelamin'])=='L') echo 'selected'; ?>>Laki-laki</option>
                                <option value="P" <?php if((isset($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : $dokter['jenis_kelamin'])=='P') echo 'selected'; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telepon *</label>
                            <input type="text" class="form-control" name="telpon" required value="<?php echo isset($_POST['telpon']) ? htmlspecialchars($_POST['telpon']) : htmlspecialchars($dokter['telpon']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : htmlspecialchars($dokter['alamat']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Spesialisasi *</label>
                            <input type="text" class="form-control" name="spesialisasi" required value="<?php echo isset($_POST['spesialisasi']) ? htmlspecialchars($_POST['spesialisasi']) : htmlspecialchars($dokter['spesialisasi']); ?>">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=dokter" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?> 