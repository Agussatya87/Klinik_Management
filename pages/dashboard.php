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
$sql = "SELECT * FROM pasien ORDER BY tgl_daftar DESC LIMIT 5";
$recent_patients = fetchAll($sql);

// Recent procedures
$sql = "SELECT t.*, p.nama as nama_pasien, d.nama as nama_dokter 
        FROM tindakan t 
        JOIN pasien p ON t.idpasien = p.idpasien 
        LEFT JOIN dokter d ON t.iddokter = d.iddokter 
        ORDER BY t.idtindakan DESC LIMIT 5";
$recent_procedures = fetchAll($sql);

// Get available rooms
$sql = "SELECT COUNT(*) as total FROM ruang WHERE status = 'Kosong'";
$available_rooms_count = fetchOne($sql);
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
                            <div class="fs-3 fw-bold text-dark"><?php echo $stats['total_pasien']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex align-items-center gap-3">
                        <i class="bi bi-buildings fs-1 text-purple" style="color:#7c3aed;"></i>
                        <div>
                            <div class="fw-semibold text-secondary">Ruang Tersedia</div>
                            <div class="fs-3 fw-bold text-dark"><?php echo $available_rooms_count['total']; ?></div>
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
                            <div class="fs-3 fw-bold text-dark"><?php echo $stats['total_dokter']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>