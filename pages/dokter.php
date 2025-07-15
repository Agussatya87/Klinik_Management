<?php
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// Get dokter list with search and pagination
    $where_clause = "";
    $params = [];

    if (!empty($search)) {
        $where_clause = "WHERE nama LIKE ? OR spesialisasi LIKE ? OR alamat LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param, $search_param, $search_param];
    }

    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM dokter " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];

    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);

    // Get dokter data
    $sql = "SELECT * FROM dokter {$where_clause} ORDER BY iddokter DESC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $dokters = fetchAll($sql, $params);
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-person-badge text-primary"></i> Data Dokter
        </h1>
        <a href="index.php?page=dokter_add" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Tambah Dokter
        </a>
    </div>

    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="dokter">
                <div class="row">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Cari nama, spesialisasi, atau alamat..."
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

    <!-- Dokter Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Daftar Dokter (<?php echo number_format($total_records); ?> data)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($dokters)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-person text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Tidak ada data dokter</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Telepon</th>
                                <th>Alamat</th>
                                <th>Spesialisasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dokters as $d): ?>
                                <tr>
                                    <td><strong><?php echo $d['nama']; ?></strong></td>
                                    <td><?php echo $d['jenis_kelamin']; ?></td>
                                    <td><?php echo $d['telpon']; ?></td>
                                    <td><?php echo $d['alamat']; ?></td>
                                    <td><?php echo $d['spesialisasi']; ?></td>
                                    <td>
                                        <a href="pages/dokter_edit.php?iddokter=<?php echo $d['iddokter']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="pages/dokter_delete.php?iddokter=<?php echo $d['iddokter']; ?>" class="btn btn-sm btn-outline-danger">
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
                    <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=dokter&search=' . urlencode($search) . '&page_num'); ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
