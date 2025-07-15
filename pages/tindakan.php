<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Ambil parameter pencarian
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Query dasar
$sql = "SELECT t.*, p.nama AS nama_pasien, d.nama AS nama_dokter
        FROM tindakan t
        LEFT JOIN pasien p ON t.idpasien = p.idpasien
        LEFT JOIN dokter d ON t.iddokter = d.iddokter";
$params = [];

// Tambahkan filter pencarian jika ada
if (!empty($search)) {
    $sql .= " WHERE p.nama LIKE ? OR d.nama LIKE ? OR t.tindakan LIKE ?";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param];
}

$sql .= " ORDER BY t.idtindakan DESC";
$tindakans = fetchAll($sql, $params);
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

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" id="searchForm">
                <input type="hidden" name="page" value="tindakan">
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="searchInput"
                                   placeholder="Cari pasien, dokter, atau tindakan..."
                                   value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
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
                            <th>ID Tindakan</th>
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
                        <?php if (empty($tindakans)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">Tidak ada data tindakan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tindakans as $t): ?>
                                <tr>
                                    <td><span class="badge bg-info text-dark"><?php echo 'TM' . str_pad($t['idtindakan'], 3, '0', STR_PAD_LEFT); ?></span></td>
                                    <td><?= htmlspecialchars($t['nama_pasien']) ?></td>
                                    <td><?= htmlspecialchars($t['kriteria']) ?></td>
                                    <td><?= htmlspecialchars($t['tindakan']) ?></td>
                                    <td><?= htmlspecialchars($t['nama_dokter'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($t['fasilitas'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($t['keputusan_keluarga']) ?></td>
                                    <td>
                                        <a href="/Klinik_Management/index.php?page=tindakan_edit&idtindakan=<?= $t['idtindakan'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="pages/tindakan_delete.php?idtindakan=<?= $t['idtindakan'] ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
