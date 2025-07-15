<?php
require_once __DIR__ . '/../config/database.php';

// Disable foreign key checks
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

// List of tables to truncate
$tables = ['rekam_medis', 'tindakan', 'ruang', 'pasien', 'dokter', 'users'];

foreach ($tables as $table) {
    $pdo->exec("TRUNCATE TABLE `$table`");
}

// Enable foreign key checks
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "Database has been reset. All data deleted and IDs reset to 1."; 