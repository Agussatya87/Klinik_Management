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

// Get doctor data for confirmation
$sql = "SELECT * FROM dokter WHERE iddokter = ?";
$doctor = fetchOne($sql, [$iddokter]);

if (!$doctor) {
    setFlashMessage('danger', 'Data dokter tidak ditemukan');
    header('Location: /Klinik_Management/index.php?page=dokter');
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Check if doctor is referenced in rekam_medis (which will prevent deletion)
    $check_rekam_medis = "SELECT COUNT(*) as count FROM rekam_medis WHERE iddokter = ?";
    $rekam_medis_count = fetchOne($check_rekam_medis, [$iddokter]);
    
    if ($rekam_medis_count['count'] > 0) {
        setFlashMessage('danger', 'Dokter tidak dapat dihapus karena masih memiliki data terkait di rekam medis');
        header('Location: /Klinik_Management/index.php?page=dokter');
        exit();
    }
    
    // Check tindakan records (will be set to NULL automatically due to ON DELETE SET NULL)
    $check_tindakan = "SELECT COUNT(*) as count FROM tindakan WHERE iddokter = ?";
    $tindakan_count = fetchOne($check_tindakan, [$iddokter]);
    
    // Delete the doctor
    $sql = "DELETE FROM dokter WHERE iddokter = ?";
    executeQuery($sql, [$iddokter]);
    
    $message = 'Data dokter berhasil dihapus';
    if ($tindakan_count['count'] > 0) {
        $message .= '. Catatan: ' . $tindakan_count['count'] . ' data tindakan medis terkait telah diupdate (dokter diset tidak ada)';
    }
    
    setFlashMessage('success', $message);
    header('Location: /Klinik_Management/index.php?page=dokter');
    exit();
} elseif (isset($_POST['confirm']) && $_POST['confirm'] === 'no') {
    header('Location: /Klinik_Management/index.php?page=dokter');
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Konfirmasi Hapus Data Dokter</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-danger">
                        <h6 class="alert-heading">
                            <i class="bi bi-exclamation-triangle-fill"></i> PERINGATAN!
                        </h6>
                        <p class="mb-0">
                            <strong>Tindakan ini tidak dapat dibatalkan!</strong> Menghapus data dokter akan mempengaruhi data tindakan medis yang terkait.
                        </p>
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus data dokter berikut?</p>
                    
                    <div class="alert alert-info">
                        <h6>Detail Dokter:</h6>
                        <ul class="list-unstyled mb-0">
                            <li><strong>Nama:</strong> <?= htmlspecialchars($doctor['nama']) ?></li>
                            <li><strong>Jenis Kelamin:</strong> <?= $doctor['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></li>
                            <li><strong>Telepon:</strong> <?= htmlspecialchars($doctor['telpon']) ?></li>
                            <li><strong>Alamat:</strong> <?= htmlspecialchars($doctor['alamat'] ?? 'Tidak ada') ?></li>
                            <li><strong>Spesialisasi:</strong> <?= htmlspecialchars($doctor['spesialisasi'] ?? 'Tidak ada') ?></li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="confirm" value="yes">
                        <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        <a href="/Klinik_Management/index.php?page=dokter" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 