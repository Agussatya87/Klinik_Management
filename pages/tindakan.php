<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $pasien_id = (int)$_POST['pasien_id'];
        $dokter_id = (int)$_POST['dokter_id'];
        $ruang_id = (int)$_POST['ruang_id'];
        $jenis_tindakan = sanitizeInput($_POST['jenis_tindakan']);
        $diagnosis = sanitizeInput($_POST['diagnosis']);
        $tanggal = $_POST['tanggal'];
        $waktu = $_POST['waktu'];
        $catatan = sanitizeInput($_POST['catatan']);
        $biaya = (float)$_POST['biaya'];
        
        $errors = validateRequired($_POST, ['pasien_id', 'dokter_id', 'ruang_id', 'jenis_tindakan', 'diagnosis', 'tanggal', 'waktu']);
        
        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO tindakan (pasien_id, dokter_id, ruang_id, jenis_tindakan, diagnosis, tanggal, waktu, catatan, biaya, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                executeQuery($sql, [$pasien_id, $dokter_id, $ruang_id, $jenis_tindakan, $diagnosis, $tanggal, $waktu, $catatan, $biaya]);
                setFlashMessage('success', 'Data tindakan berhasil ditambahkan');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE tindakan SET pasien_id = ?, dokter_id = ?, ruang_id = ?, jenis_tindakan = ?, 
                        diagnosis = ?, tanggal = ?, waktu = ?, catatan = ?, biaya = ? WHERE id = ?";
                executeQuery($sql, [$pasien_id, $dokter_id, $ruang_id, $jenis_tindakan, $diagnosis, $tanggal, $waktu, $catatan, $biaya, $id]);
                setFlashMessage('success', 'Data tindakan berhasil diperbarui');
            }
            header('Location: index.php?page=tindakan');
            exit();
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM tindakan WHERE id = ?";
        executeQuery($sql, [$id]);
        setFlashMessage('success', 'Data tindakan berhasil dihapus');
        header('Location: index.php?page=tindakan');
        exit();
    }
}

// Get procedure data for edit
$procedure = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM tindakan WHERE id = ?";
    $procedure = fetchOne($sql, [$id]);
    if (!$procedure) {
        setFlashMessage('danger', 'Data tindakan tidak ditemukan');
        header('Location: index.php?page=tindakan');
        exit();
    }
}

// Get procedures list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE p.nama LIKE ? OR d.nama LIKE ? OR t.jenis_tindakan LIKE ? OR t.diagnosis LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }
    
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM tindakan t 
                  JOIN pasien p ON t.pasien_id = p.id 
                  JOIN dokter d ON t.dokter_id = d.id " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];
    
    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);
    
    // Get procedures
    $sql = "SELECT t.*, p.nama as nama_pasien, p.no_rm, d.nama as nama_dokter, r.nama as nama_ruang 
            FROM tindakan t 
            JOIN pasien p ON t.pasien_id = p.id 
            JOIN dokter d ON t.dokter_id = d.id 
            JOIN ruang r ON t.ruang_id = r.id 
            {$where_clause} 
            ORDER BY t.tanggal DESC, t.waktu DESC 
            LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $procedures = fetchAll($sql, $params);
}

// Get data for dropdowns
$sql = "SELECT id, nama, no_rm FROM pasien ORDER BY nama";
$patients = fetchAll($sql);

$sql = "SELECT id, nama, spesialisasi FROM dokter ORDER BY nama";
$doctors = fetchAll($sql);

$sql = "SELECT id, nama, jenis FROM ruang ORDER BY nama";
$rooms = fetchAll($sql);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-clipboard2-pulse text-primary"></i> Data Tindakan
        </h1>
        <a href="index.php?page=tindakan&action=add" class="btn btn-primary">
            <i class="bi bi-clipboard-plus"></i> Tambah Tindakan
        </a>
    </div>

    <?php if ($action == 'list'): ?>
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="tindakan">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari pasien, dokter, jenis tindakan, atau diagnosis..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="index.php?page=tindakan" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Procedures Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Daftar Tindakan (<?php echo number_format($total_records); ?> data)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($procedures)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-clipboard2-pulse text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada data tindakan</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Pasien</th>
                                    <th>Dokter</th>
                                    <th>Ruang</th>
                                    <th>Jenis Tindakan</th>
                                    <th>Diagnosis</th>
                                    <th>Biaya</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($procedures as $proc): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo formatDate($proc['tanggal']); ?></strong><br>
                                            <small class="text-muted"><?php echo $proc['waktu']; ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo $proc['nama_pasien']; ?></strong><br>
                                            <small class="text-muted"><?php echo $proc['no_rm']; ?></small>
                                        </td>
                                        <td><?php echo $proc['nama_dokter']; ?></td>
                                        <td><?php echo $proc['nama_ruang']; ?></td>
                                        <td><?php echo $proc['jenis_tindakan']; ?></td>
                                        <td><?php echo $proc['diagnosis']; ?></td>
                                        <td>Rp <?php echo number_format($proc['biaya'], 0, ',', '.'); ?></td>
                                        <td>
                                            <a href="index.php?page=tindakan&action=edit&id=<?php echo $proc['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $proc['id']; ?>, '<?php echo $proc['jenis_tindakan']; ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=tindakan&search=' . urlencode($search) . '&page_num'); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($action == 'add' || $action == 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-<?php echo $action == 'add' ? 'clipboard-plus' : 'pencil'; ?>"></i>
                    <?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Data Tindakan
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <?php if ($action == 'edit'): ?>
                        <input type="hidden" name="id" value="<?php echo $procedure['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pasien_id" class="form-label">Pasien *</label>
                                <select class="form-select" id="pasien_id" name="pasien_id" required>
                                    <option value="">Pilih Pasien</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id']; ?>" 
                                                <?php echo ($procedure['pasien_id'] ?? '') == $patient['id'] ? 'selected' : ''; ?>>
                                            <?php echo $patient['nama']; ?> (<?php echo $patient['no_rm']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="dokter_id" class="form-label">Dokter *</label>
                                <select class="form-select" id="dokter_id" name="dokter_id" required>
                                    <option value="">Pilih Dokter</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>" 
                                                <?php echo ($procedure['dokter_id'] ?? '') == $doctor['id'] ? 'selected' : ''; ?>>
                                            <?php echo $doctor['nama']; ?> (<?php echo $doctor['spesialisasi']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ruang_id" class="form-label">Ruang *</label>
                                <select class="form-select" id="ruang_id" name="ruang_id" required>
                                    <option value="">Pilih Ruang</option>
                                    <?php foreach ($rooms as $room): ?>
                                        <option value="<?php echo $room['id']; ?>" 
                                                <?php echo ($procedure['ruang_id'] ?? '') == $room['id'] ? 'selected' : ''; ?>>
                                            <?php echo $room['nama']; ?> (<?php echo $room['jenis']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_tindakan" class="form-label">Jenis Tindakan *</label>
                                <input type="text" class="form-control" id="jenis_tindakan" name="jenis_tindakan" 
                                       value="<?php echo $procedure['jenis_tindakan'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal" class="form-label">Tanggal *</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                       value="<?php echo $procedure['tanggal'] ?? date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="waktu" class="form-label">Waktu *</label>
                                <input type="time" class="form-control" id="waktu" name="waktu" 
                                       value="<?php echo $procedure['waktu'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="diagnosis" class="form-label">Diagnosis *</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2" required><?php echo $procedure['diagnosis'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="biaya" class="form-label">Biaya (Rp)</label>
                                <input type="number" class="form-control" id="biaya" name="biaya" 
                                       value="<?php echo $procedure['biaya'] ?? '0'; ?>" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2"><?php echo $procedure['catatan'] ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=tindakan" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> <?php echo $action == 'add' ? 'Simpan' : 'Update'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus data tindakan <strong id="procedureName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="procedureId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('procedureId').value = id;
    document.getElementById('procedureName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script> 