<?php
// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function loginUser($username, $password) {
    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $user = fetchOne($sql, [$username, md5($password)]);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function logoutUser() {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Validation functions
function validateRequired($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty(trim($data[$field]))) {
            $errors[] = ucfirst($field) . " is required";
        }
    }
    return $errors;
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Date functions
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Flash messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Pagination
function getPagination($total_records, $records_per_page, $current_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $offset = ($current_page - 1) * $records_per_page;
    
    return [
        'total_pages' => $total_pages,
        'offset' => $offset,
        'current_page' => $current_page,
        'records_per_page' => $records_per_page
    ];
}

// Generate pagination HTML
function generatePagination($total_pages, $current_page, $base_url) {
    if ($total_pages <= 1) return '';
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page - 1) . '">Previous</a></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page + 1) . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

// Room status management functions
function updateRoomStatus($idruang, $status) {
    if ($idruang) {
        $sql = "UPDATE ruang SET status = ? WHERE idruang = ?";
        executeQuery($sql, [$status, $idruang]);
    }
}

function updateRoomStatusOnMedicalRecordChange($old_ruang_id, $new_ruang_id) {
    // If there was a previous room, check if it's still being used
    if ($old_ruang_id) {
        $check_old_room = "SELECT COUNT(*) as count FROM rekam_medis WHERE idruang = ?";
        $old_room_count = fetchOne($check_old_room, [$old_ruang_id]);
        
        if ($old_room_count['count'] == 0) {
            // No other medical records using this room, set it to empty
            updateRoomStatus($old_ruang_id, 'Kosong');
        }
    }
    
    // If there's a new room, set it to occupied
    if ($new_ruang_id) {
        updateRoomStatus($new_ruang_id, 'Terisi');
    }
}

function getAvailableRooms() {
    return fetchAll("SELECT idruang, nama_ruang FROM ruang WHERE status = 'Kosong' ORDER BY nama_ruang");
}

function getAllRooms() {
    return fetchAll("SELECT idruang, nama_ruang, status FROM ruang ORDER BY nama_ruang");
}
?> 