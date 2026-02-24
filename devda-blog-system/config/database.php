<?php
/**
 * devDA Blog System - Database Helper Functions
 * Các hàm hỗ trợ làm việc với JSON database
 */

require_once __DIR__ . '/config.php';

// ============================================
// JSON FILE OPERATIONS
// ============================================

/**
 * Đọc file JSON
 * @param string $filename Tên file (không cần .json)
 * @return array|null
 */
function readJSON($filename) {
    $path = DATA_DIR . $filename . '.json';
    
    if (!file_exists($path)) {
        return null;
    }
    
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    
    return $data ?? [];
}

/**
 * Ghi dữ liệu vào file JSON
 * @param string $filename Tên file (không cần .json)
 * @param array $data Dữ liệu cần ghi
 * @return bool
 */
function writeJSON($filename, $data) {
    $path = DATA_DIR . $filename . '.json';
    
    // Kiểm tra thư mục tồn tại
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($json === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        return false;
    }
    
    $result = file_put_contents($path, $json, LOCK_EX);
    
    if ($result === false) {
        error_log("Failed to write file: $path");
        return false;
    }
    
    return true;
}

/**
 * Lấy một mục từ file JSON theo ID
 * @param string $filename Tên file
 * @param string $key Key chứa mảng (ví dụ 'users', 'posts')
 * @param string $id ID cần tìm
 * @return array|null
 */
function getItem($filename, $key, $id) {
    $data = readJSON($filename);
    
    if (!isset($data[$key])) {
        return null;
    }
    
    foreach ($data[$key] as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    
    return null;
}

/**
 * Thêm một mục vào file JSON
 * @param string $filename Tên file
 * @param string $key Key chứa mảng
 * @param array $item Mục cần thêm
 * @return bool
 */
function addItem($filename, $key, $item) {
    $data = readJSON($filename) ?? [];
    
    // Initialize key nếu chưa tồn tại
    if (!isset($data[$key])) {
        $data[$key] = [];
    }
    
    // Ensure item has an id
    if (!isset($item['id'])) {
        $item['id'] = uniqid(substr($key, 0, -1) . '_');
    }
    
    // Add timestamps if not present
    if (!isset($item['created_at'])) {
        $item['created_at'] = date('Y-m-d H:i:s');
    }
    if (!isset($item['updated_at'])) {
        $item['updated_at'] = date('Y-m-d H:i:s');
    }
    
    $data[$key][] = $item;
    
    return writeJSON($filename, $data);
}

/**
 * Cập nhật một mục trong file JSON
 * @param string $filename Tên file
 * @param string $key Key chứa mảng
 * @param string $id ID của mục cần cập nhật
 * @param array $updates Dữ liệu cần cập nhật
 * @return bool
 */
function updateItem($filename, $key, $id, $updates) {
    $data = readJSON($filename);
    
    if (!isset($data[$key])) {
        return false;
    }
    
    $found = false;
    foreach ($data[$key] as &$item) {
        if ($item['id'] == $id) {
            $item = array_merge($item, $updates);
            $item['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return false;
    }
    
    return writeJSON($filename, $data);
}

/**
 * Xóa một mục từ file JSON
 * @param string $filename Tên file
 * @param string $key Key chứa mảng
 * @param string $id ID của mục cần xóa
 * @return bool
 */
function deleteItem($filename, $key, $id) {
    $data = readJSON($filename);
    
    if (!isset($data[$key])) {
        return false;
    }
    
    foreach ($data[$key] as $index => $item) {
        if ($item['id'] == $id) {
            unset($data[$key][$index]);
            // Reindex array
            $data[$key] = array_values($data[$key]);
            return writeJSON($filename, $data);
        }
    }
    
    return false;
}

/**
 * Lấy tất cả mục từ key
 * @param string $filename Tên file
 * @param string $key Key chứa mảng
 * @return array
 */
function getItems($filename, $key) {
    $data = readJSON($filename);
    
    if (!isset($data[$key])) {
        return [];
    }
    
    return $data[$key];
}

// ============================================
// SEARCH & FILTER FUNCTIONS
// ============================================

/**
 * Tìm kiếm trong mảng JSON
 * @param array $items Mảng items
 * @param string $field Trường cần tìm
 * @param string $value Giá trị cần tìm
 * @return array
 */
function filterItems($items, $field, $value) {
    return array_filter($items, function($item) use ($field, $value) {
        return isset($item[$field]) && $item[$field] == $value;
    });
}

/**
 * Tìm kiếm text trong mảng
 * @param array $items Mảng items
 * @param array $fields Các trường cần tìm
 * @param string $query Text cần tìm
 * @return array
 */
function searchItems($items, $fields, $query) {
    $query = strtolower($query);
    
    return array_filter($items, function($item) use ($fields, $query) {
        foreach ($fields as $field) {
            if (isset($item[$field]) && 
                stripos($item[$field], $query) !== false) {
                return true;
            }
        }
        return false;
    });
}

// ============================================
// PAGINATION FUNCTIONS
// ============================================

/**
 * Phân trang mảng
 * @param array $items Mảng items
 * @param int $page Trang (bắt đầu từ 1)
 * @param int $per_page Số items mỗi trang
 * @return array ['items' => [...], 'total' => 50, 'page' => 1, 'per_page' => 10, 'pages' => 5]
 */
function paginate($items, $page = 1, $per_page = 10) {
    $total = count($items);
    $pages = ceil($total / $per_page);
    
    // Validate page
    if ($page < 1) $page = 1;
    if ($page > $pages && $pages > 0) $page = $pages;
    
    $offset = ($page - 1) * $per_page;
    $paginated = array_slice($items, $offset, $per_page);
    
    return [
        'items' => $paginated,
        'total' => $total,
        'page' => $page,
        'per_page' => $per_page,
        'pages' => $pages
    ];
}

// ============================================
// SORT FUNCTIONS
// ============================================

/**
 * Sắp xếp mảng items theo trường
 * @param array $items Mảng items
 * @param string $field Trường cần sắp xếp
 * @param string $order 'asc' hoặc 'desc'
 * @return array
 */
function sortItems($items, $field, $order = 'desc') {
    usort($items, function($a, $b) use ($field, $order) {
        if (!isset($a[$field]) || !isset($b[$field])) {
            return 0;
        }
        
        $cmp = 0;
        
        // Compare dates
        if (strtotime($a[$field]) && strtotime($b[$field])) {
            $cmp = strtotime($a[$field]) - strtotime($b[$field]);
        } else {
            // String/number comparison
            $cmp = strcmp((string)$a[$field], (string)$b[$field]);
        }
        
        return $order === 'asc' ? $cmp : -$cmp;
    });
    
    return $items;
}

?>
