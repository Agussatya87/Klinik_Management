<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Fetch all doctors
$dokters = fetchAll("SELECT * FROM dokter ORDER BY iddokter DESC");
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-person-badge text-primary"></i> Data Dokter
        </h1>
        <a href="/Klinik_Management/index.php?page=dokter_add" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah Dokter
        </a>
    </div>
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Dokter</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jenis Kelamin</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Spesialisasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dokters as $d): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['nama']); ?></td>
                            <td><?php echo $d['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                            <td><?php echo htmlspecialchars($d['telpon']); ?></td>
                            <td><?php echo htmlspecialchars($d['alamat']); ?></td>
                            <td><?php echo htmlspecialchars($d['spesialisasi']); ?></td>
                            <td>
                                <a href="/Klinik_Management/index.php?page=dokter_edit&iddokter=<?php echo $d['iddokter']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 