<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
        $sql = "INSERT INTO dokter (nama, jenis_kelamin, telpon, alamat, spesialisasi) VALUES (?, ?, ?, ?, ?)";
        executeQuery($sql, [$nama, $jenis_kelamin, $telpon, $alamat, $spesialisasi]);
        setFlashMessage('success', 'Data dokter berhasil ditambahkan');
        header('Location: /Klinik_Management/index.php?page=dokter');
        exit();
    }
}
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Data Dokter</h5>
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
                            <label class="form-label" for="nama">Nama *</label>
                            <input type="text" class="form-control" id="nama" name="nama" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="jenis_kelamin">Jenis Kelamin *</label>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" <?php if(isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='L') echo 'selected'; ?>>Laki-laki</option>
                                <option value="P" <?php if(isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='P') echo 'selected'; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="telpon">Telepon *</label>
                            <input type="text" class="form-control" id="telpon" name="telpon" required value="<?php echo isset($_POST['telpon']) ? htmlspecialchars($_POST['telpon']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="alamat">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="spesialisasi">Spesialisasi *</label>
                            <input type="text" class="form-control" id="spesialisasi" name="spesialisasi" required value="<?php echo isset($_POST['spesialisasi']) ? htmlspecialchars($_POST['spesialisasi']) : ''; ?>">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=dokter" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 