<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $kode_icd = sanitizeInput($_POST['kode_icd']);
        $nama_diagnosis = sanitizeInput($_POST['nama_diagnosis']);
        $kategori = sanitizeInput($_POST['kategori']);
        $deskripsi = sanitizeInput($_POST['deskripsi']);
        
        $errors = validateRequired($_POST, ['kode_icd', 'nama_diagnosis', 'kategori']);
        
        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO diagnosis (kode_icd, nama_diagnosis, kategori, deskripsi, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
                executeQuery($sql, [$kode_icd, $nama_diagnosis, $kategori, $deskripsi]);
                setFlashMessage('success', 'Data diagnosis berhasil ditambahkan');
            } else {
                $id = (int)$_POST['id'];
                $sql = "UPDATE diagnosis SET kode_icd = ?, nama_diagnosis = ?, kategori = ?, deskripsi = ? WHERE id = ?";
                executeQuery($sql, [$kode_icd, $nama_diagnosis, $kategori, $deskripsi, $id]);
                setFlashMessage('success', 'Data diagnosis berhasil diperbarui');
            }
            header('Location: index.php?page=diagnosis');
            exit();
        }
    } elseif ($action == 'delete') {
        $id = (int)$_POST['id'];
        $sql = "DELETE FROM diagnosis WHERE id = ?";
        executeQuery($sql, [$id]);
        setFlashMessage('success', 'Data diagnosis berhasil dihapus');
        header('Location: index.php?page=diagnosis');
        exit();
    }
}

// Get diagnosis data for edit
$diagnosis = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "SELECT * FROM diagnosis WHERE id = ?";
    $diagnosis = fetchOne($sql, [$id]);
    if (!$diagnosis) {
        setFlashMessage('danger', 'Data diagnosis tidak ditemukan');
        header('Location: index.php?page=diagnosis');
        exit();
    }
}

// Get diagnosis list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];
    
    if (!empty($search)) {
        $where_clause = "WHERE kode_icd LIKE ? OR nama_diagnosis LIKE ? OR kategori LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param];
    }
    
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM diagnosis " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];
    
    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);
    
    // Get diagnosis
    $sql = "SELECT * FROM diagnosis {$where_clause} ORDER BY kode_icd ASC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $diagnoses = fetchAll($sql, $params);
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-file-medical text-primary"></i> Data Diagnosis
        </h1>
        <a href="index.php?page=diagnosis&action=add" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Diagnosis
        </a>
    </div>

    <?php if ($action == 'list'): ?>
        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <input type="hidden" name="page" value="diagnosis">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Cari kode ICD, nama diagnosis, atau kategori..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="index.php?page=diagnosis" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Diagnosis Table -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    Daftar Diagnosis (<?php echo number_format($total_records); ?> data)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($diagnoses)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-file-medical text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">Tidak ada data diagnosis</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode ICD</th>
                                    <th>Nama Diagnosis</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($diagnoses as $diag): ?>
                                    <tr>
                                        <td><strong><?php echo $diag['kode_icd']; ?></strong></td>
                                        <td><?php echo $diag['nama_diagnosis']; ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $diag['kategori']; ?></span>
                                        </td>
                                        <td><?php echo $diag['deskripsi']; ?></td>
                                        <td><?php echo formatDate($diag['created_at']); ?></td>
                                        <td>
                                            <a href="index.php?page=diagnosis&action=edit&id=<?php echo $diag['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $diag['id']; ?>, '<?php echo $diag['nama_diagnosis']; ?>')">
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
                        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=diagnosis&search=' . urlencode($search) . '&page_num'); ?>
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
                    <i class="bi bi-<?php echo $action == 'add' ? 'plus-circle' : 'pencil'; ?>"></i>
                    <?php echo $action == 'add' ? 'Tambah' : 'Edit'; ?> Data Diagnosis
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
                        <input type="hidden" name="id" value="<?php echo $diagnosis['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kode_icd" class="form-label">Kode ICD *</label>
                                <input type="text" class="form-control" id="kode_icd" name="kode_icd" 
                                       value="<?php echo $diagnosis['kode_icd'] ?? ''; ?>" required>
                                <small class="form-text text-muted">Contoh: A00.0, B01.1, C50.9</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="kategori" class="form-label">Kategori *</label>
                                <select class="form-select" id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Penyakit Infeksi" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Infeksi' ? 'selected' : ''; ?>>Penyakit Infeksi</option>
                                    <option value="Penyakit Jantung" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Jantung' ? 'selected' : ''; ?>>Penyakit Jantung</option>
                                    <option value="Penyakit Paru-paru" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Paru-paru' ? 'selected' : ''; ?>>Penyakit Paru-paru</option>
                                    <option value="Penyakit Pencernaan" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Pencernaan' ? 'selected' : ''; ?>>Penyakit Pencernaan</option>
                                    <option value="Penyakit Saraf" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Saraf' ? 'selected' : ''; ?>>Penyakit Saraf</option>
                                    <option value="Penyakit Kulit" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Kulit' ? 'selected' : ''; ?>>Penyakit Kulit</option>
                                    <option value="Penyakit Mata" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Mata' ? 'selected' : ''; ?>>Penyakit Mata</option>
                                    <option value="Penyakit THT" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit THT' ? 'selected' : ''; ?>>Penyakit THT</option>
                                    <option value="Penyakit Gigi" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Gigi' ? 'selected' : ''; ?>>Penyakit Gigi</option>
                                    <option value="Penyakit Tulang" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Tulang' ? 'selected' : ''; ?>>Penyakit Tulang</option>
                                    <option value="Penyakit Kanker" <?php echo ($diagnosis['kategori'] ?? '') == 'Penyakit Kanker' ? 'selected' : ''; ?>>Penyakit Kanker</option>
                                    <option value="Lainnya" <?php echo ($diagnosis['kategori'] ?? '') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nama_diagnosis" class="form-label">Nama Diagnosis *</label>
                        <input type="text" class="form-control" id="nama_diagnosis" name="nama_diagnosis" 
                               value="<?php echo $diagnosis['nama_diagnosis'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo $diagnosis['deskripsi'] ?? ''; ?></textarea>
                        <small class="form-text text-muted">Penjelasan detail tentang diagnosis (opsional)</small>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=diagnosis" class="btn btn-secondary">
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
                <p>Apakah Anda yakin ingin menghapus data diagnosis <strong id="diagnosisName"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak dapat dibatalkan!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="diagnosisId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('diagnosisId').value = id;
    document.getElementById('diagnosisName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script> 