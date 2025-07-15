<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = ['rekam_medis', 'tindakan', 'pasien', 'dokter', 'ruang'];
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "<div style='font-family:sans-serif;padding:2rem;background:#e9f7ef;border-left:5px solid #2ecc71'>
        ✅ Semua data berhasil direset.<br>ID sekarang dimulai dari 1 untuk setiap tabel.
    </div>";
} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif;padding:2rem;color:red;'>❌ Gagal reset: " . $e->getMessage() . "</div>";
}
?>
