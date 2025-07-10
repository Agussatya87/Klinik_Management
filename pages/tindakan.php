<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Fetch all tindakan with patient name
$sql = "SELECT t.*, p.nama as nama_pasien FROM tindakan t LEFT JOIN pasien p ON t.idpasien = p.idpasien ORDER BY t.idtindakan DESC";
$tindakans = fetchAll($sql);
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-clipboard2-pulse text-primary"></i> Data Tindakan Medis
        </h1>
        <a href="/Klinik_Management/index.php?page=tindakan_add" class="btn btn-primary">
            <i class="bi bi-plus"></i> Tambah Tindakan
        </a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Tindakan</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Pasien</th>
                            <th>Kriteria</th>
                            <th>Tindakan</th>
                            <th>Dokter</th>
                            <th>Fasilitas</th>
                            <th>Keputusan Keluarga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tindakans as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['nama_pasien']); ?></td>
                            <td><?php echo htmlspecialchars($t['kriteria']); ?></td>
                            <td><?php echo htmlspecialchars($t['tindakan']); ?></td>
                            <td><?php echo htmlspecialchars($t['dokter']); ?></td>
                            <td><?php echo htmlspecialchars($t['fasilitas']); ?></td>
                            <td><?php echo htmlspecialchars($t['keputusan_keluarga']); ?></td>
                            <td>
                                <a href="/Klinik_Management/index.php?page=tindakan_edit&idtindakan=<?php echo $t['idtindakan']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <a href="#" class="btn btn-sm btn-outline-danger disabled"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 