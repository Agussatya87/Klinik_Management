<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit();
}

// Get the requested page
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Define allowed pages
$allowed_pages = [
    'dashboard',
    'pasien',
    'pasien_add', // allow dedicated add patient page
    'tindakan',
    'tindakan_add',
    'tindakan_edit',
    'rekam_medis',
    'rekam_medis_add',
    'rekam_medis_edit',
    'dokter',
    'dokter_add',
    'dokter_edit',
    'ruang',
    'logout'
];

// Validate page parameter
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Include header
include 'includes/header.php';

// Include the requested page
$page_file = "pages/{$page}.php";
if (file_exists($page_file)) {
    include $page_file;
} else {
    echo '<div class="container mt-4"><div class="alert alert-danger">Page not found!</div></div>';
}

// Include footer
include 'includes/footer.php';
?> 