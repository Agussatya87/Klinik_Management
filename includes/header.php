<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Management System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/Klinik_Management/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/Klinik_Management/index.php">
                <img src="/Klinik_Management/assets/logo.jpg" alt="Logo" width="48" height="48" class="me-2" style="object-fit:contain;">
                <span class="fw-bold fs-4 text-dark">Klinik Management System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold d-flex align-items-center gap-1 <?php echo ($page == 'dashboard') ? 'active' : ''; ?>" href="/Klinik_Management/index.php">
                            <i class="bi bi-activity"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold d-flex align-items-center gap-1 <?php echo ($page == 'pasien') ? 'active' : ''; ?>" href="/Klinik_Management/index.php?page=pasien">
                            <i class="bi bi-people"></i> Data Pasien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold d-flex align-items-center gap-1 <?php echo ($page == 'tindakan') ? 'active' : ''; ?>" href="/Klinik_Management/index.php?page=tindakan">
                            <i class="bi bi-calendar2-check"></i> Tindakan Medis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold d-flex align-items-center gap-1 <?php echo ($page == 'rekam_medis') ? 'active' : ''; ?>" href="/Klinik_Management/index.php?page=rekam_medis">
                            <i class="bi bi-file-earmark-medical"></i> Rekam Medis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold d-flex align-items-center gap-1 <?php echo ($page == 'dokter') ? 'active' : ''; ?>" href="/Klinik_Management/index.php?page=dokter">
                            <i class="bi bi-person-badge"></i> Data Dokter
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3 fw-semibold d-flex align-items-center gap-1 <?php echo ($page == 'ruang') ? 'active' : ''; ?>" href="/Klinik_Management/index.php?page=ruang">
                            <i class="bi bi-buildings"></i> Ruangan
                        </a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="/Klinik_Management/index.php?page=logout" class="btn btn-outline-secondary rounded-3 px-4 fw-semibold">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php
    $flash = getFlashMessage();
    if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4"> 