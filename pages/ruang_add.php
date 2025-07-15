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

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_ruang = sanitizeInput($_POST['nama_ruang']);
    $status = $_POST['status'];

    $errors = validateRequired($_POST, ['nama_ruang', 'status']);

    if (empty($errors)) {
        $sql = "INSERT INTO ruang (nama_ruang, status) VALUES (?, ?)";
        executeQuery($sql, [$nama_ruang, $status]);
        setFlashMessage('success', 'Data ruang berhasil ditambahkan');
        header('Location: /Klinik_Management/index.php?page=ruang');
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
                    <h5 class="mb-0">Tambah Data Ruang</h5>
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
                            <label class="form-label" for="nama_ruang">Nama Ruang *</label>
                            <input type="text" class="form-control" id="nama_ruang" name="nama_ruang" required value="<?php echo isset($_POST['nama_ruang']) ? htmlspecialchars($_POST['nama_ruang']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="status">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="Kosong" <?php if (isset($_POST['status']) && $_POST['status'] == 'Kosong') echo 'selected'; ?>>Kosong</option>
                                <option value="Terisi" <?php if (isset($_POST['status']) && $_POST['status'] == 'Terisi') echo 'selected'; ?>>Terisi</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=ruang" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?> 