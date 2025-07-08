<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $nama = sanitizeInput($_POST['nama']);
        $no_rm = sanitizeInput($_POST['no_rm']);
        $alamat = sanitizeInput($_POST['alamat']);
        $telepon = sanitizeInput($_POST['telepon']);
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        
        $errors = validateRequired($_POST, ['nama', 'no_rm', 'alamat', 'telepon', 'tanggal_lahir', 'jenis_kelamin']);
        
        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO pasien (nama, no_rm, alamat, telepon, tanggal_lahir, jenis_kelamin, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, NOW())";
                executeQuery($sql, [$nama, $no_rm, $alamat, $telepon, $tanggal_lahir, $jenis_kelamin]);
                setFlashMessage('success', 'Data pasien berhasil ditambahkan');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE pasien SET nama = ?, no_rm = ?, alamat = ?, telepon = ?, 
                        tanggal_lahir = ?, jenis_kelamin = ? WHERE id = ?";
                executeQuery($sql, [$nama, $no_rm, $alamat, $telepon, $tanggal_lahir, $jenis_kelamin, $id]);
                setFlashMessage('success', 'Data pasien berhasil diperbarui');
            }
            header('Location: index.php?page=pasien');
            exit();
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM pasien WHERE id = ?";
        executeQuery($sql, [$id]);
        setFlashMessage('success', 'Data pasien berhasil dihapus');
        header('Location: index.php?page=pasien');
        exit();
    }
}

// Get patient data for edit
$patient = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM pasien WHERE id = ?";
    $patient = fetchOne($sql, [$id]);
    if (!$patient) {
        setFlashMessage('danger', 'Data pasien tidak ditemukan');
        header('Location: index.php?page=pasien');
        exit();
    }
}

// Get patients list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE nama LIKE ? OR no_rm LIKE ? OR alamat LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param];
    }
    
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM pasien " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];
    
    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);
    
    // Get patients
    $sql = "SELECT * FROM pasien {$where_clause} ORDER BY created_at DESC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $patients = fetchAll($sql, $params);
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people text-primary"></i> Data Pasien
        </h1>
        <a href="index.php?page=pasien&action=add" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah Pasien
        </a>
    </div>

    <?php if ($action == 'list'): ?>
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="pasien">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari nama, no RM, atau alamat..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="index.php?page=pasien" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Patients Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Daftar Pasien (<?php echo number_format($total_records); ?> data)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($patients)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada data pasien</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. RM</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Telepon</th>
                                    <th>Alamat</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $p): ?>
                                    <tr>
                                        <td><strong><?php echo $p['no_rm']; ?></strong></td>
                                        <td><?php echo $p['nama']; ?></td>
                                        <td><?php echo $p['jenis_kelamin']; ?></td>
                                        <td><?php echo formatDate($p['tanggal_lahir']); ?></td>
                                        <td><?php echo $p['telepon']; ?></td>
                                        <td><?php echo $p['alamat']; ?></td>
                                        <td><?php echo formatDate($p['created_at']); ?></td>
                                        <td>
                                            <a href="index.php?page=pasien&action=edit&id=<?php echo $p['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $p['id']; ?>, '<?php echo $p['nama']; ?>')">
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
                        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=pasien&search=' . urlencode($search) . '&page_num'); ?>
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
                    <i class="bi bi-<?php echo $action == 'add' ? 'person-plus' : 'pencil'; ?>"></i>
                    <?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Data Pasien
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
                        <input type="hidden" name="id" value="<?php echo $patient['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?php echo $patient['nama'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_rm" class="form-label">Nomor Rekam Medis *</label>
                                <input type="text" class="form-control" id="no_rm" name="no_rm" 
                                       value="<?php echo $patient['no_rm'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir *</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                       value="<?php echo $patient['tanggal_lahir'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki" <?php echo ($patient['jenis_kelamin'] ?? '') == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                    <option value="Perempuan" <?php echo ($patient['jenis_kelamin'] ?? '') == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telepon" class="form-label">Nomor Telepon *</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" 
                               value="<?php echo $patient['telepon'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat *</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo $patient['alamat'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=pasien" class="btn btn-secondary">
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
                <p>Apakah Anda yakin ingin menghapus data pasien <strong id="patientName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="patientId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('patientId').value = id;
    document.getElementById('patientName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script> 