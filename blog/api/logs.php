<?php
/**
 * DevDA Blog System - Logs API (Admin only)
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    jsonResponse(['error' => 'Không có quyền'], 403);
}

$action = $_GET['action'] ?? null;

switch($action) {
    case 'list':
        handleList();
        break;
    case 'clear':
        handleClear();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Liệt kê logs
 */
function handleList() {
    global $db;
    $logs = $db->getAll('logs');
    
    // Sắp xếp theo ngày mới nhất
    usort($logs, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Giới hạn 1000 log gần đây
    $logs = array_slice($logs, 0, 1000);
    
    jsonResponse(['logs' => $logs]);
}

/**
 * Xoá tất cả logs (admin only)
 */
function handleClear() {
    global $db;
    
    if ($db->write('logs', [])) {
        jsonResponse(['success' => true, 'message' => 'Logs đã được xoá']);
    }
    
    jsonResponse(['error' => 'Lỗi khi xoá logs'], 500);
}
?>
