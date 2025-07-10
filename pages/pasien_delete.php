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

if (!isset($_GET['idpasien']) || !is_numeric($_GET['idpasien'])) {
    setFlashMessage('danger', 'ID pasien tidak valid');
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

$idpasien = (int)$_GET['idpasien'];

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $sql = "SELECT * FROM pasien WHERE idpasien = ?";
    $patient = fetchOne($sql, [$idpasien]);
    if ($patient) {
        $sql = "DELETE FROM pasien WHERE idpasien = ?";
        executeQuery($sql, [$idpasien]);
        setFlashMessage('success', 'Data pasien berhasil dihapus');
    } else {
        setFlashMessage('danger', 'Data pasien tidak ditemukan');
    }
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
} else {
    // If not confirmed, just redirect
    header('Location: /Klinik_Management/index.php?page=pasien');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Konfirmasi Hapus Pasien</h5>
                </div>
                <div class="card-body">
                    <p>Apakah Anda yakin ingin menghapus data pasien berikut?</p>
                    <ul>
                        <li><strong>Nama:</strong> <?php echo htmlspecialchars($patient['nama']); ?></li>
                        <li><strong>Telepon:</strong> <?php echo htmlspecialchars($patient['telpon']); ?></li>
                        <li><strong>Alamat:</strong> <?php echo htmlspecialchars($patient['alamat']); ?></li>
                    </ul>
                    <form method="POST" action="">
                        <button type="submit" name="confirm" value="yes" class="btn btn-danger">Ya, Hapus</button>
                        <button type="submit" name="confirm" value="no" class="btn btn-secondary">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?> 