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

if (!isset($_GET['idruang']) || !is_numeric($_GET['idruang'])) {
    setFlashMessage('danger', 'ID ruang tidak valid');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

$idruang = (int)$_GET['idruang'];
$ruang = fetchOne("SELECT * FROM ruang WHERE idruang = ?", [$idruang]);
if (!$ruang) {
    setFlashMessage('danger', 'Data ruang tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_ruang = sanitizeInput($_POST['nama_ruang']);
    $status = $_POST['status'];

    $errors = validateRequired($_POST, ['nama_ruang', 'status']);

    if (empty($errors)) {
        $sql = "UPDATE ruang SET nama_ruang=?, status=? WHERE idruang=?";
        executeQuery($sql, [$nama_ruang, $status, $idruang]);
        setFlashMessage('success', 'Data ruang berhasil diperbarui');
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
                    <h5 class="mb-0">Edit Data Ruang</h5>
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
                            <input type="text" class="form-control" id="nama_ruang" name="nama_ruang" required value="<?php echo isset($_POST['nama_ruang']) ? htmlspecialchars($_POST['nama_ruang']) : htmlspecialchars($ruang['nama_ruang']); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="status">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="Kosong" <?php if((isset($_POST['status']) ? $_POST['status'] : $ruang['status'])=='Kosong') echo 'selected'; ?>>Kosong</option>
                                <option value="Terisi" <?php if((isset($_POST['status']) ? $_POST['status'] : $ruang['status'])=='Terisi') echo 'selected'; ?>>Terisi</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/Klinik_Management/index.php?page=ruang" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?> 