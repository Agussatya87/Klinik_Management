<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $nama = sanitizeInput($_POST['nama']);
        $nip = sanitizeInput($_POST['nip']);
        $spesialisasi = sanitizeInput($_POST['spesialisasi']);
        $alamat = sanitizeInput($_POST['alamat']);
        $telepon = sanitizeInput($_POST['telepon']);
        $email = sanitizeInput($_POST['email']);
        $status = $_POST['status'];
        
        $errors = validateRequired($_POST, ['nama', 'nip', 'spesialisasi', 'alamat', 'telepon', 'email', 'status']);
        
        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO dokter (nama, nip, spesialisasi, alamat, telepon, email, status, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                executeQuery($sql, [$nama, $nip, $spesialisasi, $alamat, $telepon, $email, $status]);
                setFlashMessage('success', 'Data dokter berhasil ditambahkan');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE dokter SET nama = ?, nip = ?, spesialisasi = ?, alamat = ?, 
                        telepon = ?, email = ?, status = ? WHERE id = ?";
                executeQuery($sql, [$nama, $nip, $spesialisasi, $alamat, $telepon, $email, $status, $id]);
                setFlashMessage('success', 'Data dokter berhasil diperbarui');
            }
            header('Location: index.php?page=dokter');
            exit();
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM dokter WHERE id = ?";
        executeQuery($sql, [$id]);
        setFlashMessage('success', 'Data dokter berhasil dihapus');
        header('Location: index.php?page=dokter');
        exit();
    }
}

// Get doctor data for edit
$doctor = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM dokter WHERE id = ?";
    $doctor = fetchOne($sql, [$id]);
    if (!$doctor) {
        setFlashMessage('danger', 'Data dokter tidak ditemukan');
        header('Location: index.php?page=dokter');
        exit();
    }
}

// Get doctors list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE nama LIKE ? OR nip LIKE ? OR spesialisasi LIKE ? OR email LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param, $search_param];
    }
    
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM dokter " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];
    
    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);
    
    // Get doctors
    $sql = "SELECT * FROM dokter {$where_clause} ORDER BY nama ASC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $doctors = fetchAll($sql, $params);
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-person-badge text-primary"></i> Data Dokter
        </h1>
        <a href="index.php?page=dokter&action=add" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah Dokter
        </a>
    </div>

    <?php if ($action == 'list'): ?>
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="dokter">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari nama, NIP, spesialisasi, atau email..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="index.php?page=dokter" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Doctors Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Daftar Dokter (<?php echo number_format($total_records); ?> data)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($doctors)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-person-badge text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada data dokter</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama</th>
                                    <th>Spesialisasi</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Status</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doc): ?>
                                    <tr>
                                        <td><strong><?php echo $doc['nip']; ?></strong></td>
                                        <td><?php echo $doc['nama']; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $doc['spesialisasi']; ?></span>
                                        </td>
                                        <td><?php echo $doc['email']; ?></td>
                                        <td><?php echo $doc['telepon']; ?></td>
                                        <td>
                                            <?php if ($doc['status'] == 'Aktif'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($doc['created_at']); ?></td>
                                        <td>
                                            <a href="index.php?page=dokter&action=edit&id=<?php echo $doc['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $doc['id']; ?>, '<?php echo $doc['nama']; ?>')">
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
                        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=dokter&search=' . urlencode($search) . '&page_num'); ?>
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
                    <?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Data Dokter
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
                        <input type="hidden" name="id" value="<?php echo $doctor['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?php echo $doctor['nama'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nip" class="form-label">NIP *</label>
                                <input type="text" class="form-control" id="nip" name="nip" 
                                       value="<?php echo $doctor['nip'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="spesialisasi" class="form-label">Spesialisasi *</label>
                                <select class="form-select" id="spesialisasi" name="spesialisasi" required>
                                    <option value="">Pilih Spesialisasi</option>
                                    <option value="Dokter Umum" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Umum' ? 'selected' : ''; ?>>Dokter Umum</option>
                                    <option value="Dokter Gigi" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Gigi' ? 'selected' : ''; ?>>Dokter Gigi</option>
                                    <option value="Dokter Mata" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Mata' ? 'selected' : ''; ?>>Dokter Mata</option>
                                    <option value="Dokter THT" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter THT' ? 'selected' : ''; ?>>Dokter THT</option>
                                    <option value="Dokter Kulit" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Kulit' ? 'selected' : ''; ?>>Dokter Kulit</option>
                                    <option value="Dokter Jantung" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Jantung' ? 'selected' : ''; ?>>Dokter Jantung</option>
                                    <option value="Dokter Paru-paru" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Paru-paru' ? 'selected' : ''; ?>>Dokter Paru-paru</option>
                                    <option value="Dokter Saraf" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Saraf' ? 'selected' : ''; ?>>Dokter Saraf</option>
                                    <option value="Dokter Bedah" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Bedah' ? 'selected' : ''; ?>>Dokter Bedah</option>
                                    <option value="Dokter Anak" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Anak' ? 'selected' : ''; ?>>Dokter Anak</option>
                                    <option value="Dokter Kandungan" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Kandungan' ? 'selected' : ''; ?>>Dokter Kandungan</option>
                                    <option value="Dokter Ortopedi" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Ortopedi' ? 'selected' : ''; ?>>Dokter Ortopedi</option>
                                    <option value="Dokter Radiologi" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Radiologi' ? 'selected' : ''; ?>>Dokter Radiologi</option>
                                    <option value="Dokter Anestesi" <?php echo ($doctor['spesialisasi'] ?? '') == 'Dokter Anestesi' ? 'selected' : ''; ?>>Dokter Anestesi</option>
                                    <option value="Lainnya" <?php echo ($doctor['spesialisasi'] ?? '') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif" <?php echo ($doctor['status'] ?? '') == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?php echo ($doctor['status'] ?? '') == 'Tidak Aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $doctor['email'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telepon" class="form-label">Nomor Telepon *</label>
                                <input type="text" class="form-control" id="telepon" name="telepon" 
                                       value="<?php echo $doctor['telepon'] ?? ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat *</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo $doctor['alamat'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=dokter" class="btn btn-secondary">
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
                <p>Apakah Anda yakin ingin menghapus data dokter <strong id="doctorName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="doctorId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('doctorId').value = id;
    document.getElementById('doctorName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script> 