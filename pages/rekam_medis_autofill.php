<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idpasien = isset($_GET['idpasien']) ? (int)$_GET['idpasien'] : 0;
$result = [
    'idtindakan' => '',
    'iddokter' => ''
];
if ($idpasien) {
    // Get the latest tindakan for this patient
    $tindakan = fetchOne("SELECT idtindakan, iddokter FROM tindakan WHERE idpasien = ? ORDER BY idtindakan DESC LIMIT 1", [$idpasien]);
    if ($tindakan) {
        $result['idtindakan'] = $tindakan['idtindakan'];
        $result['iddokter'] = $tindakan['iddokter'];
    }
}
echo json_encode($result); 