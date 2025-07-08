<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $nama = sanitizeInput($_POST['nama']);
        $jenis = sanitizeInput($_POST['jenis']);
        $kapasitas = (int)$_POST['kapasitas'];
        $lantai = sanitizeInput($_POST['lantai']);
        $status = $_POST['status'];
        $deskripsi = sanitizeInput($_POST['deskripsi']);
        
        $errors = validateRequired($_POST, ['nama', 'jenis', 'kapasitas', 'lantai', 'status']);
        
        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO ruang (nama, jenis, kapasitas, lantai, status, deskripsi, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
                executeQuery($sql, [$nama, $jenis, $kapasitas, $lantai, $status, $deskripsi]);
                setFlashMessage('success', 'Data ruang berhasil ditambahkan');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE ruang SET nama = ?, jenis = ?, kapasitas = ?, lantai = ?, 
                        status = ?, deskripsi = ? WHERE id = ?";
                executeQuery($sql, [$nama, $jenis, $kapasitas, $lantai, $status, $deskripsi, $id]);
                setFlashMessage('success', 'Data ruang berhasil diperbarui');
            }
            header('Location: index.php?page=ruang');
            exit();
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM ruang WHERE id = ?";
        executeQuery($sql, [$id]);
        setFlashMessage('success', 'Data ruang berhasil dihapus');
        header('Location: index.php?page=ruang');
        exit();
    }
}

// Get room data for edit
$room = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM ruang WHERE id = ?";
    $room = fetchOne($sql, [$id]);
    if (!$room) {
        setFlashMessage('danger', 'Data ruang tidak ditemukan');
        header('Location: index.php?page=ruang');
        exit();
    }
}

// Get rooms list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE nama LIKE ? OR jenis LIKE ? OR lantai LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param];
    }
    
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM ruang " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];
    
    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);
    
    // Get rooms
    $sql = "SELECT * FROM ruang {$where_clause} ORDER BY lantai ASC, nama ASC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $rooms = fetchAll($sql, $params);
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-building text-primary"></i> Manajemen Ruang
        </h1>
        <a href="index.php?page=ruang&action=add" class="btn btn-primary">
            <i class="bi bi-building-add"></i> Tambah Ruang
        </a>
    </div>

    <?php if ($action == 'list'): ?>
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="ruang">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari nama ruang, jenis, atau lantai..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="index.php?page=ruang" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rooms Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Daftar Ruang (<?php echo number_format($total_records); ?> data)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($rooms)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-building text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada data ruang</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Ruang</th>
                                    <th>Jenis</th>
                                    <th>Lantai</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $r): ?>
                                    <tr>
                                        <td><strong><?php echo $r['nama']; ?></strong></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $r['jenis']; ?></span>
                                        </td>
                                        <td>Lantai <?php echo $r['lantai']; ?></td>
                                        <td><?php echo $r['kapasitas']; ?> orang</td>
                                        <td>
                                            <?php if ($r['status'] == 'Tersedia'): ?>
                                                <span class="badge bg-success">Tersedia</span>
                                            <?php elseif ($r['status'] == 'Terpakai'): ?>
                                                <span class="badge bg-warning">Terpakai</span>
                                            <?php elseif ($r['status'] == 'Maintenance'): ?>
                                                <span class="badge bg-danger">Maintenance</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $r['status']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $r['deskripsi']; ?></td>
                                        <td><?php echo formatDate($r['created_at']); ?></td>
                                        <td>
                                            <a href="index.php?page=ruang&action=edit&id=<?php echo $r['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $r['id']; ?>, '<?php echo $r['nama']; ?>')">
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
                        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=ruang&search=' . urlencode($search) . '&page_num'); ?>
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
                    <i class="bi bi-<?php echo $action == 'add' ? 'building-add' : 'pencil'; ?>"></i>
                    <?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Data Ruang
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
                        <input type="hidden" name="id" value="<?php echo $room['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Ruang *</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?php echo $room['nama'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis" class="form-label">Jenis Ruang *</label>
                                <select class="form-select" id="jenis" name="jenis" required>
                                    <option value="">Pilih Jenis Ruang</option>
                                    <option value="Ruang Konsultasi" <?php echo ($room['jenis'] ?? '') == 'Ruang Konsultasi' ? 'selected' : ''; ?>>Ruang Konsultasi</option>
                                    <option value="Ruang Pemeriksaan" <?php echo ($room['jenis'] ?? '') == 'Ruang Pemeriksaan' ? 'selected' : ''; ?>>Ruang Pemeriksaan</option>
                                    <option value="Ruang Operasi" <?php echo ($room['jenis'] ?? '') == 'Ruang Operasi' ? 'selected' : ''; ?>>Ruang Operasi</option>
                                    <option value="Ruang Rawat Inap" <?php echo ($room['jenis'] ?? '') == 'Ruang Rawat Inap' ? 'selected' : ''; ?>>Ruang Rawat Inap</option>
                                    <option value="Ruang ICU" <?php echo ($room['jenis'] ?? '') == 'Ruang ICU' ? 'selected' : ''; ?>>Ruang ICU</option>
                                    <option value="Ruang Laboratorium" <?php echo ($room['jenis'] ?? '') == 'Ruang Laboratorium' ? 'selected' : ''; ?>>Ruang Laboratorium</option>
                                    <option value="Ruang Radiologi" <?php echo ($room['jenis'] ?? '') == 'Ruang Radiologi' ? 'selected' : ''; ?>>Ruang Radiologi</option>
                                    <option value="Ruang Farmasi" <?php echo ($room['jenis'] ?? '') == 'Ruang Farmasi' ? 'selected' : ''; ?>>Ruang Farmasi</option>
                                    <option value="Ruang Tunggu" <?php echo ($room['jenis'] ?? '') == 'Ruang Tunggu' ? 'selected' : ''; ?>>Ruang Tunggu</option>
                                    <option value="Ruang Administrasi" <?php echo ($room['jenis'] ?? '') == 'Ruang Administrasi' ? 'selected' : ''; ?>>Ruang Administrasi</option>
                                    <option value="Lainnya" <?php echo ($room['jenis'] ?? '') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="lantai" class="form-label">Lantai *</label>
                                <select class="form-select" id="lantai" name="lantai" required>
                                    <option value="">Pilih Lantai</option>
                                    <option value="1" <?php echo ($room['lantai'] ?? '') == '1' ? 'selected' : ''; ?>>Lantai 1</option>
                                    <option value="2" <?php echo ($room['lantai'] ?? '') == '2' ? 'selected' : ''; ?>>Lantai 2</option>
                                    <option value="3" <?php echo ($room['lantai'] ?? '') == '3' ? 'selected' : ''; ?>>Lantai 3</option>
                                    <option value="4" <?php echo ($room['lantai'] ?? '') == '4' ? 'selected' : ''; ?>>Lantai 4</option>
                                    <option value="5" <?php echo ($room['lantai'] ?? '') == '5' ? 'selected' : ''; ?>>Lantai 5</option>
                                    <option value="Basement" <?php echo ($room['lantai'] ?? '') == 'Basement' ? 'selected' : ''; ?>>Basement</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="kapasitas" class="form-label">Kapasitas *</label>
                                <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                                       value="<?php echo $room['kapasitas'] ?? '1'; ?>" min="1" required>
                                <small class="form-text text-muted">Jumlah orang yang dapat ditampung</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Tersedia" <?php echo ($room['status'] ?? '') == 'Tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                    <option value="Terpakai" <?php echo ($room['status'] ?? '') == 'Terpakai' ? 'selected' : ''; ?>>Terpakai</option>
                                    <option value="Maintenance" <?php echo ($room['status'] ?? '') == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Tidak Tersedia" <?php echo ($room['status'] ?? '') == 'Tidak Tersedia' ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo $room['deskripsi'] ?? ''; ?></textarea>
                        <small class="form-text text-muted">Informasi tambahan tentang ruang (opsional)</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=ruang" class="btn btn-secondary">
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
                <p>Apakah Anda yakin ingin menghapus data ruang <strong id="roomName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="roomId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('roomId').value = id;
    document.getElementById('roomName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script> 