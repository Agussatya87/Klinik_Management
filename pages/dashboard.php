<?php
// Get dashboard statistics
$stats = [];

// Total patients
$sql = "SELECT COUNT(*) as total FROM pasien";
$result = fetchOne($sql);
$stats['total_pasien'] = $result['total'];

// Total doctors
$sql = "SELECT COUNT(*) as total FROM dokter";
$result = fetchOne($sql);
$stats['total_dokter'] = $result['total'];

// Recent patients
$sql = "SELECT * FROM pasien ORDER BY created_at DESC LIMIT 5";
$recent_patients = fetchAll($sql);

// Recent procedures
$sql = "SELECT t.*, p.nama as nama_pasien, d.nama as nama_dokter 
        FROM tindakan t 
        JOIN pasien p ON t.pasien_id = p.id 
        JOIN dokter d ON t.dokter_id = d.id 
        ORDER BY t.tanggal DESC LIMIT 5";
$recent_procedures = fetchAll($sql);
?>

<div class="container-fluid">
    <!-- Dashboard Top Cards -->
    <div class="dashboard-cards">
        <div class="row g-4 mb-5 mt-2">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi bi-people fs-1 text-primary"></i>
                        <div>
                            <div class="fw-semibold text-secondary">Pasien Aktif</div>
                            <div class="fs-3 fw-bold text-dark">156</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi bi-activity fs-1 text-success"></i>
                        <div>
                            <div class="fw-semibold text-secondary">Tindakan Hari Ini</div>
                            <div class="fs-3 fw-bold text-dark">42</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi bi-building fs-1 text-purple" style="color:#7c3aed;"></i>
                        <div>
                            <div class="fw-semibold text-secondary">Ruang Tersedia</div>
                            <div class="fs-3 fw-bold text-dark">8</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi bi-person-badge fs-1 text-info"></i>
                        <div>
                            <div class="fw-semibold text-secondary">Dokter Bertugas</div>
                            <div class="fs-3 fw-bold text-dark">24</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Patients -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-people"></i> Pasien Terbaru
                    </h6>
                    <a href="index.php?page=pasien" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_patients)): ?>
                        <p class="text-muted">Belum ada data pasien</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>No. RM</th>
                                        <th>Tanggal Daftar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_patients as $patient): ?>
                                        <tr>
                                            <td><?php echo $patient['nama']; ?></td>
                                            <td><?php echo $patient['no_rm']; ?></td>
                                            <td><?php echo formatDate($patient['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Procedures -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clipboard2-pulse"></i> Tindakan Terbaru
                    </h6>
                    <a href="index.php?page=tindakan" class="btn btn-sm btn-primary">
                        Lihat Semua
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_procedures)): ?>
                        <p class="text-muted">Belum ada data tindakan</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Pasien</th>
                                        <th>Dokter</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_procedures as $procedure): ?>
                                        <tr>
                                            <td><?php echo $procedure['nama_pasien']; ?></td>
                                            <td><?php echo $procedure['nama_dokter']; ?></td>
                                            <td><?php echo formatDate($procedure['tanggal']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-lightning"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="index.php?page=pasien&action=add" class="btn btn-outline-primary w-100">
                                <i class="bi bi-person-plus"></i><br>
                                Tambah Pasien
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="index.php?page=tindakan&action=add" class="btn btn-outline-success w-100">
                                <i class="bi bi-clipboard-plus"></i><br>
                                Tambah Tindakan
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="index.php?page=dokter&action=add" class="btn btn-outline-info w-100">
                                <i class="bi bi-person-badge"></i><br>
                                Tambah Dokter
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="index.php?page=ruang&action=add" class="btn btn-outline-warning w-100">
                                <i class="bi bi-building-add"></i><br>
                                Tambah Ruang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 