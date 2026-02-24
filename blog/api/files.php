<?php
/**
 * DevDA Blog System - Files API
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'upload':
        handleUpload();
        break;
    case 'list':
        handleList();
        break;
    case 'delete':
        handleDelete();
        break;
    default:
        jsonResponse(['error' => 'Action không hợp lệ'], 400);
}

/**
 * Upload file
 */
function handleUpload() {
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    if (!isset($_FILES['file'])) {
        jsonResponse(['error' => 'Không có file được upload'], 400);
    }
    
    $file = $_FILES['file'];
    $type = $_POST['type'] ?? 'docs'; // images, pdf, docs
    
    // Validate loại file
    $allowed = [
        'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'pdf' => ['pdf'],
        'docs' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar']
    ];
    
    if (!isset($allowed[$type])) {
        jsonResponse(['error' => 'Loại file không hợp lệ'], 400);
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed[$type])) {
        jsonResponse(['error' => 'Định dạng file không được hỗ trợ'], 400);
    }
    
    // Kiểm tra kích thước (max 50MB)
    if ($file['size'] > 50 * 1024 * 1024) {
        jsonResponse(['error' => 'File quá lớn (max 50MB)'], 400);
    }
    
    $uploadDir = UPLOADS_DIR . '/' . $type;
    $fileName = sanitizeFileName($file['name']);
    $fileName = time() . '_' . $fileName;
    $filePath = $uploadDir . '/' . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $fileRecord = $db->create('files', [
            'filename' => $fileName,
            'original_name' => $file['name'],
            'type' => $type,
            'size' => $file['size'],
            'uploader_id' => $_SESSION['user_id'],
            'path' => '/blog/uploads/' . $type . '/' . $fileName,
            'downloads' => 0
        ]);
        
        if ($fileRecord) {
            $db->create('logs', [
                'action' => 'upload_file',
                'user_id' => $_SESSION['user_id'],
                'details' => 'Upload file: ' . $file['name']
            ]);
            
            jsonResponse([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => $fileRecord,
                'url' => $fileRecord['path']
            ], 201);
        }
    }
    
    jsonResponse(['error' => 'Lỗi khi upload file'], 500);
}

/**
 * Danh sách files
 */
function handleList() {
    $type = $_GET['type'] ?? null;
    $uploaderId = $_GET['uploader_id'] ?? null;
    
    $files = $db->filter('files', function($f) use ($type, $uploaderId) {
        if ($type && $f['type'] !== $type) return false;
        if ($uploaderId && $f['uploader_id'] !== $uploaderId) return false;
        return true;
    });
    
    jsonResponse(['files' => $files]);
}

/**
 * Xoá file
 */
function handleDelete() {
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'Bạn cần đăng nhập'], 401);
    }
    
    $fileId = $_POST['id'] ?? $_GET['id'] ?? null;
    
    if (!$fileId) {
        jsonResponse(['error' => 'ID file không hợp lệ'], 400);
    }
    
    $file = $db->find('files', 'id', $fileId);
    
    if (!$file) {
        jsonResponse(['error' => 'File không tồn tại'], 404);
    }
    
    // Kiểm tra quyền
    if ($file['uploader_id'] !== $_SESSION['user_id'] && !isAdmin()) {
        jsonResponse(['error' => 'Bạn không có quyền xoá file này'], 403);
    }
    
    // Xoá file từ hệ thống
    $filePath = UPLOADS_DIR . '/' . $file['type'] . '/' . $file['filename'];
    @unlink($filePath);
    
    // Xoá record
    if ($db->delete('files', $fileId)) {
        $db->create('logs', [
            'action' => 'delete_file',
            'user_id' => $_SESSION['user_id'],
            'details' => 'Xoá file: ' . $file['original_name']
        ]);
        
        jsonResponse(['success' => true, 'message' => 'File đã được xoá']);
    }
    
    jsonResponse(['error' => 'Lỗi khi xoá file'], 500);
}
?>
