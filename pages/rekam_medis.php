<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $idpasien = (int)$_POST['idpasien'];
        $iddokter = (int)$_POST['iddokter'];
        $idruang = isset($_POST['idruang']) ? (int)$_POST['idruang'] : null;
        $diagnosis = sanitizeInput($_POST['diagnosis']);
        $idtindakan = isset($_POST['idtindakan']) ? (int)$_POST['idtindakan'] : null;

        $errors = validateRequired($_POST, ['idpasien', 'iddokter', 'diagnosis']);

        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO rekam_medis (idpasien, iddokter, idruang, diagnosis, idtindakan) VALUES (?, ?, ?, ?, ?)";
                executeQuery($sql, [$idpasien, $iddokter, $idruang, $diagnosis, $idtindakan]);
                setFlashMessage('success', 'Data rekam medis berhasil ditambahkan');
            } else {
                $idrm = (int)$_POST['idrm'];
                $sql = "UPDATE rekam_medis SET idpasien = ?, iddokter = ?, idruang = ?, diagnosis = ?, idtindakan = ? WHERE idrm = ?";
                executeQuery($sql, [$idpasien, $iddokter, $idruang, $diagnosis, $idtindakan, $idrm]);
                setFlashMessage('success', 'Data rekam medis berhasil diperbarui');
            }
            header('Location: index.php?page=rekam_medis');
            exit();
        }
    } elseif ($action == 'delete') {
        $idrm = (int)$_POST['idrm'];
        $sql = "DELETE FROM rekam_medis WHERE idrm = ?";
        executeQuery($sql, [$idrm]);
        setFlashMessage('success', 'Data rekam medis berhasil dihapus');
        header('Location: index.php?page=rekam_medis');
        exit();
    }
}

// Get medical record data for edit
$rekam_medis = null;
if ($action == 'edit' && isset($_GET['idrm'])) {
    $idrm = (int)$_GET['idrm'];
    $sql = "SELECT * FROM rekam_medis WHERE idrm = ?";
    $rekam_medis = fetchOne($sql, [$idrm]);
    if (!$rekam_medis) {
        setFlashMessage('danger', 'Data rekam medis tidak ditemukan');
        header('Location: index.php?page=rekam_medis');
        exit();
    }
}

// Get medical records list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];

    if (!empty($search)) {
        $where_clause = "WHERE p.nama LIKE ? OR d.nama LIKE ? OR r.nama_ruang LIKE ? OR t.tindakan LIKE ? OR rm.diagnosis LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param, $search_param, $search_param];
    }

    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total
        FROM rekam_medis rm
        JOIN pasien p ON rm.idpasien = p.idpasien
        JOIN dokter d ON rm.iddokter = d.iddokter
        LEFT JOIN ruang r ON rm.idruang = r.idruang
        LEFT JOIN tindakan t ON rm.idtindakan = t.idtindakan
        {$where_clause}";
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];

    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);

    // Get medical records
    $sql = "SELECT rm.*, p.nama as nama_pasien, d.nama as nama_dokter, r.nama_ruang, t.tindakan as tindakan
            FROM rekam_medis rm
            JOIN pasien p ON rm.idpasien = p.idpasien
            JOIN dokter d ON rm.iddokter = d.iddokter
            LEFT JOIN ruang r ON rm.idruang = r.idruang
            LEFT JOIN tindakan t ON rm.idtindakan = t.idtindakan
            {$where_clause}
            ORDER BY rm.idrm DESC
            LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $medical_records = fetchAll($sql, $params);
}

// Get data for dropdowns
$patients = fetchAll("SELECT idpasien, nama FROM pasien ORDER BY nama");
$doctors = fetchAll("SELECT iddokter, nama FROM dokter ORDER BY nama");
$rooms = fetchAll("SELECT idruang, nama_ruang FROM ruang ORDER BY nama_ruang");
$tindakans = fetchAll("SELECT idtindakan, tindakan FROM tindakan ORDER BY tindakan");
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-file-earmark-medical text-primary"></i> Data Rekam Medis
        </h1>
        <a href="/Klinik_Management/index.php?page=rekam_medis_add" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Rekam Medis
        </a>
    </div>

    <?php $flash = getFlashMessage(); if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?>"> <?php echo $flash['message']; ?> </div>
    <?php endif; ?>

    <?php if ($action == 'list'): ?>
        <div class="card mb-4">
            <div class="card-header">
                <form class="d-flex" method="get" action="">
                    <input type="hidden" name="page" value="rekam_medis">
                    <input type="text" name="search" class="form-control me-2" placeholder="Cari pasien, dokter, ruang, tindakan, atau diagnosis..." value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-primary" type="submit">Cari</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Ruang</th>
                                <th>Tindakan</th>
                                <th>Diagnosis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($medical_records)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Tidak ada data rekam medis</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($medical_records as $rm): ?>
                                    <tr>
                                        <td><?php echo $rm['idrm']; ?></td>
                                        <td><?php echo $rm['nama_pasien']; ?></td>
                                        <td><?php echo $rm['nama_dokter']; ?></td>
                                        <td><?php echo $rm['nama_ruang']; ?></td>
                                        <td><?php echo $rm['tindakan']; ?></td>
                                        <td><?php echo $rm['diagnosis']; ?></td>
                                        <td>
                                            <a href="/Klinik_Management/pages/rekam_medis_edit.php?idrm=<?php echo $rm['idrm']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="/Klinik_Management/pages/rekam_medis_delete.php?idrm=<?php echo $rm['idrm']; ?>" class="btn btn-sm btn-danger">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($action == 'add' || $action == 'edit'): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Rekam Medis</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="idrm" value="<?php echo $rekam_medis['idrm']; ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="idpasien" class="form-label">Pasien *</label>
                        <select class="form-select" id="idpasien" name="idpasien" required>
                            <option value="">Pilih Pasien</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['idpasien']; ?>" <?php echo ($rekam_medis['idpasien'] ?? '') == $p['idpasien'] ? 'selected' : ''; ?>><?php echo $p['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="iddokter" class="form-label">Dokter *</label>
                        <select class="form-select" id="iddokter" name="iddokter" required>
                            <option value="">Pilih Dokter</option>
                            <?php foreach ($doctors as $d): ?>
                                <option value="<?php echo $d['iddokter']; ?>" <?php echo ($rekam_medis['iddokter'] ?? '') == $d['iddokter'] ? 'selected' : ''; ?>><?php echo $d['nama']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="idruang" class="form-label">Ruang</label>
                        <select class="form-select" id="idruang" name="idruang">
                            <option value="">Pilih Ruang</option>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?php echo $r['idruang']; ?>" <?php echo ($rekam_medis['idruang'] ?? '') == $r['idruang'] ? 'selected' : ''; ?>><?php echo $r['nama_ruang']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="idtindakan" class="form-label">Tindakan</label>
                        <select class="form-select" id="idtindakan" name="idtindakan">
                            <option value="">Pilih Tindakan</option>
                            <?php foreach ($tindakans as $t): ?>
                                <option value="<?php echo $t['idtindakan']; ?>" <?php echo ($rekam_medis['idtindakan'] ?? '') == $t['idtindakan'] ? 'selected' : ''; ?>><?php echo $t['tindakan']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">Diagnosis *</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" required><?php echo $rekam_medis['diagnosis'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $action == 'add' ? 'Tambah' : 'Simpan'; ?></button>
                    <a href="index.php?page=rekam_medis" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div> 