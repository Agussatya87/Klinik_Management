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
    $pekerjaan = sanitizeInput($_POST['pekerjaan']);
    $tmp_lahir = sanitizeInput($_POST['tmp_lahir']);
    $tgl_lahir = $_POST['tgl_lahir'];
    $telpon = sanitizeInput($_POST['telpon']);
    $alamat = sanitizeInput($_POST['alamat']);
    $tgl_daftar = $_POST['tgl_daftar'] ?? date('Y-m-d');

    $errors = validateRequired($_POST, ['nama', 'jenis_kelamin', 'telpon']);

    if (empty($errors)) {
        $sql = "INSERT INTO pasien (nama, jenis_kelamin, pekerjaan, tmp_lahir, tgl_lahir, telpon, alamat, tgl_daftar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        executeQuery($sql, [$nama, $jenis_kelamin, $pekerjaan, $tmp_lahir, $tgl_lahir, $telpon, $alamat, $tgl_daftar]);
        setFlashMessage('success', 'Data pasien berhasil ditambahkan');
        header('Location: /Klinik_Management/index.php?page=pasien');
        exit();
    }
}

// Get next auto-increment value for pasien
$next_id = 1;
$sql = "SHOW TABLE STATUS LIKE 'pasien'";
$status = fetchOne($sql);
if ($status && isset($status['Auto_increment'])) {
    $next_id = (int)$status['Auto_increment'];
}
$display_id = 'PS' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Tambah Data Pasien</h5>
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
                            <label class="form-label" for="display_id">ID Pasien</label>
                            <input type="text" class="form-control" id="display_id" name="display_id" value="<?php echo $display_id; ?>" readonly>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="nama">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="nama" name="nama" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="jenis_kelamin">Jenis Kelamin *</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="L" <?php if(isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='L') echo 'selected'; ?>>Laki-laki</option>
                                    <option value="P" <?php if(isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin']=='P') echo 'selected'; ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="pekerjaan">Pekerjaan</label>
                                <input type="text" class="form-control" id="pekerjaan" name="pekerjaan" value="<?php echo isset($_POST['pekerjaan']) ? htmlspecialchars($_POST['pekerjaan']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="tmp_lahir">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tmp_lahir" name="tmp_lahir" value="<?php echo isset($_POST['tmp_lahir']) ? htmlspecialchars($_POST['tmp_lahir']) : ''; ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="tgl_lahir">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" value="<?php echo isset($_POST['tgl_lahir']) ? $_POST['tgl_lahir'] : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="telpon">Nomor Telepon *</label>
                                <input type="text" class="form-control" id="telpon" name="telpon" required value="<?php echo isset($_POST['telpon']) ? htmlspecialchars($_POST['telpon']) : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="alamat">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="2"><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="tgl_daftar">Tanggal Daftar</label>
                            <input type="date" class="form-control" id="tgl_daftar" name="tgl_daftar" value="<?php echo isset($_POST['tgl_daftar']) ? $_POST['tgl_daftar'] : date('Y-m-d'); ?>">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=pasien" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script src="/Klinik_Management/assets/js/script.js"></script>
