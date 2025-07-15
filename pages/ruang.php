<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;

// // Handle form submissions
// if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//     if ($action == 'delete') {
//         $idruang = (int)$_POST['idruang'];
//         $sql = "DELETE FROM ruang WHERE idruang = ?";
//         executeQuery($sql, [$idruang]);
//         setFlashMessage('success', 'Data ruang berhasil dihapus');
//         header('Location: index.php?page=ruang');
//         exit();
//     }
// }

// // Get room data for edit
// $room = null;
// if ($action == 'edit' && isset($_GET['idruang'])) {
//     $idruang = (int)$_GET['idruang'];
//     $sql = "SELECT * FROM ruang WHERE idruang = ?";
//     $room = fetchOne($sql, [$idruang]);
//     if (!$room) {
//         setFlashMessage('danger', 'Data ruang tidak ditemukan');
//         header('Location: index.php?page=ruang');
//         exit();
//     }
// }

// Get rooms list with search and pagination
if ($action == 'list') {
    $where_clause = "";
    $params = [];
    if (!empty($search)) {
        $where_clause = "WHERE nama_ruang LIKE ?";
        $search_param = "%{$search}%";
        $params = [$search_param];
    }
    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM ruang " . $where_clause;
    $total_result = fetchOne($count_sql, $params);
    $total_records = $total_result['total'];
    // Pagination
    $records_per_page = 10;
    $pagination = getPagination($total_records, $records_per_page, $page_num);
    // Get rooms
    $sql = "SELECT * FROM ruang {$where_clause} ORDER BY nama_ruang ASC LIMIT {$pagination['offset']}, {$pagination['records_per_page']}";
    $rooms = fetchAll($sql, $params);
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="bi bi-buildings text-primary"></i> Manajemen Ruang
        </h1>
        <a href="pages/ruang_add.php" class="btn btn-primary">
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
                                       placeholder="Cari nama ruang..." 
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
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rooms as $r): ?>
                                    <tr>
                                        <td><strong><?php echo $r['nama_ruang']; ?></strong></td>
                                        <td>
                                            <?php if ($r['status'] == 'Kosong'): ?>
                                                <span class="badge bg-success">Kosong</span>
                                            <?php elseif ($r['status'] == 'Terisi'): ?>
                                                <span class="badge bg-danger">Terisi</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo $r['status']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="pages/ruang_edit.php?idruang=<?php echo $r['idruang']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($r['status'] == 'Terisi'): ?>
                                                <a href="pages/ruang_reset_status.php?idruang=<?php echo $r['idruang']; ?>" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Reset status ke Kosong">
                                                    <i class="bi bi-arrow-clockwise"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="pages/ruang_delete.php?idruang=<?php echo $r['idruang']; ?>" class="btn btn-sm btn-outline-danger">
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
                        <?php echo generatePagination($pagination['total_pages'], $pagination['current_page'], 'index.php?page=ruang&search=' . urlencode($search) . '&page_num'); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div> 