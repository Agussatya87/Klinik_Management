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

// Get room data for confirmation
$sql = "SELECT * FROM ruang WHERE idruang = ?";
$ruang = fetchOne($sql, [$idruang]);

if (!$ruang) {
    setFlashMessage('danger', 'Data ruang tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Check if room is referenced in rekam_medis
    $check_rekam_medis = "SELECT COUNT(*) as count FROM rekam_medis WHERE idruang = ?";
    $rekam_medis_count = fetchOne($check_rekam_medis, [$idruang]);
    
    if ($rekam_medis_count['count'] > 0) {
        setFlashMessage('danger', 'Ruang tidak dapat dihapus karena masih memiliki data terkait di rekam medis');
        header('Location: /Klinik_Management/index.php?page=ruang');
        exit();
    }
    
    // Delete the room
    $sql = "DELETE FROM ruang WHERE idruang = ?";
    executeQuery($sql, [$idruang]);
    setFlashMessage('success', 'Data ruang berhasil dihapus');
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header('Location: /Klinik_Management/index.php?page=ruang');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Konfirmasi Hapus Data Ruang</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-danger">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> PERINGATAN!
                        </h6>
                        <p class="mb-0">
                            <strong>Tindakan ini tidak dapat dibatalkan!</strong> Menghapus data ruang akan mempengaruhi data rekam medis yang terkait.
                        </p>
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus data ruang berikut?</p>
                    
                    <div class="alert alert-info">
                        <h6>Detail Ruang:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nama Ruang:</strong> <?= htmlspecialchars($ruang['nama_ruang']) ?></li>
                            <li><strong>Status:</strong> 
                                <span class="badge <?= $ruang['status'] === 'Terisi' ? 'bg-warning' : 'bg-success' ?>">
                                    <?= $ruang['status'] ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        <a href="/Klinik_Management/index.php?page=ruang" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 