<?php
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'list');
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($action == 'add' || $action == 'edit') {
        $nama = sanitizeInput($_POST['nama']);
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $pekerjaan = sanitizeInput($_POST['pekerjaan']);
        $tmp_lahir = sanitizeInput($_POST['tmp_lahir']);
        $tgl_lahir = $_POST['tgl_lahir'];
        $telpon = sanitizeInput($_POST['telpon']);
        $alamat = sanitizeInput($_POST['alamat']);
        $tgl_daftar = $_POST['tgl_daftar'] ?? date('Y-m-d');

        $errors = validateRequired($_POST, ['nama', 'jenis_kelamin', 'telpon']);

        if (empty($errors)) {
            if ($action == 'add') {
                $sql = "INSERT INTO pasien (nama, jenis_kelamin, pekerjaan, tmp_lahir, tgl_lahir, telpon, alamat, tgl_daftar) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                executeQuery($sql, [$nama, $jenis_kelamin, $pekerjaan, $tmp_lahir, $tgl_lahir, $telpon, $alamat, $tgl_daftar]);
                setFlashMessage('success', 'Data pasien berhasil ditambahkan');
            } else {
                $idpasien = (int)$_POST['idpasien'];
                $sql = "UPDATE pasien SET nama = ?, jenis_kelamin = ?, pekerjaan = ?, tmp_lahir = ?, tgl_lahir = ?, telpon = ?, alamat = ?, tgl_daftar = ? WHERE idpasien = ?";
                executeQuery($sql, [$nama, $jenis_kelamin, $pekerjaan, $tmp_lahir, $tgl_lahir, $telpon, $alamat, $tgl_daftar, $idpasien]);
                setFlashMessage('success', 'Data pasien berhasil diperbarui');
            }
            header('Location: index.php?page=pasien');
            exit();
        }
    } elseif ($action == 'delete') {
        $idpasien = (int)$_POST['idpasien'];
        $sql = "DELETE FROM pasien WHERE idpasien = ?";
        executeQuery($sql, [$idpasien]);
        setFlashMessage('success', 'Data pasien berhasil dihapus');
        header('Location: index.php?page=pasien');
        exit();
    }
}

// Get patient data for edit
$patient = null;
if ($action == 'edit' && isset($_GET['idpasien'])) {
    $idpasien = (int)$_GET['idpasien'];
    $sql = "SELECT * FROM pasien WHERE idpasien = ?";
    $patient = fetchOne($sql, [$idpasien]);
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
        $where_clause = "WHERE nama LIKE ? OR pekerjaan LIKE ? OR alamat LIKE ?";
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
    $sql = "SELECT * FROM pasien {$where_clause} ORDER BY tgl_daftar DESC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $patients = fetchAll($sql, $params);
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people text-primary"></i> Data Pasien
        </h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pasienModal" onclick="openAddPatientModal()">
            <i class="bi bi-person-plus"></i> Tambah Pasien
        </button>
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
                                       placeholder="Cari nama, pekerjaan, atau alamat..." 
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
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Pekerjaan</th>
                                    <th>Tempat Lahir</th>
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
                                        <td><strong><?php echo $p['nama']; ?></strong></td>
                                        <td><?php echo $p['jenis_kelamin']; ?></td>
                                        <td><?php echo $p['pekerjaan']; ?></td>
                                        <td><?php echo $p['tmp_lahir']; ?></td>
                                        <td><?php echo formatDate($p['tgl_lahir']); ?></td>
                                        <td><?php echo $p['telpon']; ?></td>
                                        <td><?php echo $p['alamat']; ?></td>
                                        <td><?php echo formatDate($p['tgl_daftar']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick='openEditPatientModal(<?php echo json_encode($p); ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="POST" action="" style="display:inline-block;">
                                                <input type="hidden" name="idpasien" value="<?php echo $p['idpasien']; ?>">
                                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="pasienModal" tabindex="-1" aria-labelledby="pasienModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pasienModalLabel">Tambah Data Pasien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalError"></div>
                    <form method="POST" action="" id="pasienForm">
                        <input type="hidden" name="idpasien" id="idpasien">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin *</label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pekerjaan" class="form-label">Pekerjaan</label>
                                    <input type="text" class="form-control" id="pekerjaan" name="pekerjaan">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tmp_lahir" class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" id="tmp_lahir" name="tmp_lahir">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telpon" class="form-label">Nomor Telepon *</label>
                                    <input type="text" class="form-control" id="telpon" name="telpon" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="tgl_daftar" class="form-label">Tanggal Daftar</label>
                            <input type="date" class="form-control" id="tgl_daftar" name="tgl_daftar" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Tambah</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openAddPatientModal() {
    document.getElementById('pasienModalLabel').innerText = 'Tambah Data Pasien';
    document.getElementById('submitBtn').innerText = 'Tambah';
    document.getElementById('pasienForm').reset();
    document.getElementById('idpasien').value = '';
    document.getElementById('modalError').innerHTML = '';
    document.getElementById('formAction').value = 'add';
    // Remove validation classes
    var form = document.getElementById('pasienForm');
    Array.from(form.elements).forEach(function(el) {
        el.classList.remove('is-valid', 'is-invalid');
    });
}

function openEditPatientModal(data) {
    document.getElementById('pasienModalLabel').innerText = 'Edit Data Pasien';
    document.getElementById('submitBtn').innerText = 'Simpan';
    document.getElementById('idpasien').value = data.idpasien;
    document.getElementById('nama').value = data.nama;
    document.getElementById('jenis_kelamin').value = data.jenis_kelamin;
    document.getElementById('pekerjaan').value = data.pekerjaan;
    document.getElementById('tmp_lahir').value = data.tmp_lahir;
    document.getElementById('tgl_lahir').value = data.tgl_lahir;
    document.getElementById('telpon').value = data.telpon;
    document.getElementById('alamat').value = data.alamat;
    document.getElementById('tgl_daftar').value = data.tgl_daftar;
    document.getElementById('modalError').innerHTML = '';
    document.getElementById('formAction').value = 'edit';
    // Remove validation classes
    var form = document.getElementById('pasienForm');
    Array.from(form.elements).forEach(function(el) {
        el.classList.remove('is-valid', 'is-invalid');
    });
    var pasienModal = new bootstrap.Modal(document.getElementById('pasienModal'));
    pasienModal.show();
}
</script> 