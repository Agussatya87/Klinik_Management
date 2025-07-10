<?php
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'list');
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Get patients list with search and pagination
if ($action == 'list' || $action == 'edit') {
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

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    ?>
    <div class="table-responsive" id="patientsTableWrapper">
        <table class="table table-hover" id="patientsTable">
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
                            <a href="pages/pasien_edit.php?idpasien=<?php echo $p['idpasien']; ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="pages/pasien_delete.php?idpasien=<?php echo $p['idpasien']; ?>&confirm=yes" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['total_pages'] > 1): ?>
        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=pasien&search=' . urlencode($search) . '&page_num'); ?>
    <?php endif; ?>
    <?php
    exit();
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-people text-primary"></i> Data Pasien
        </h1>
        <a href="pages/pasien_add.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah Pasien
        </a>
    </div>

    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" id="searchForm">
                <input type="hidden" name="page" value="pasien">
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" id="searchInput"
                                   placeholder="Cari nama, pekerjaan, atau alamat..."
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
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
                <div class="table-responsive" id="patientsTableWrapper">
                    <table class="table table-hover" id="patientsTable">
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
                                        <a href="pages/pasien_edit.php?idpasien=<?php echo $p['idpasien']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="pages/pasien_delete.php?idpasien=<?php echo $p['idpasien']; ?>&confirm=yes" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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
</div> 