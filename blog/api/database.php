<?php
/**
 * DevDA Blog System - JSON Database Handler
 * Tương thích x10hosting free
 */

class Database {
    private static $instance = null;
    private $dataDir = null;

    private function __construct() {
        $this->dataDir = DATA_DIR;
        $this->initializeDatabase();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Khởi tạo database nếu chưa có
     */
    private function initializeDatabase() {
        $tables = ['users', 'posts', 'comments', 'votes', 'files', 'logs'];
        
        foreach ($tables as $table) {
            $file = $this->dataDir . '/' . $table . '.json';
            
            if (!file_exists($file)) {
                $initialData = [];
                
                // Dữ liệu khởi tạo cho users
                if ($table === 'users') {
                    // Admin account mặc định
                    // Username: admin | Password: admin123
                    $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
                    
                    $initialData = [
                        [
                            'id' => 'admin_' . time(),
                            'username' => 'admin',
                            'email' => 'admin@devda.blog',
                            'password' => $adminHash,
                            'role' => 'admin',
                            'created_at' => date('Y-m-d H:i:s'),
                            'status' => 'active'
                        ]
                    ];
                }
                
                file_put_contents($file, json_encode($initialData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * Đọc dữ liệu từ file JSON
     */
    public function read($table) {
        $file = $this->dataDir . '/' . $table . '.json';
        
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    /**
     * Ghi dữ liệu vào file JSON
     */
    public function write($table, $data) {
        $file = $this->dataDir . '/' . $table . '.json';
        
        // Backup file cũ
        if (file_exists($file)) {
            @copy($file, $file . '.backup');
        }
        
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $result = file_put_contents($file, $json);
        
        return $result !== false;
    }

    /**
     * Tìm record trong table
     */
    public function find($table, $key, $value) {
        $data = $this->read($table);
        
        foreach ($data as $record) {
            if (isset($record[$key]) && $record[$key] === $value) {
                return $record;
            }
        }
        
        return null;
    }

    /**
     * Thêm record mới
     */
    public function create($table, $data) {
        $all = $this->read($table);
        
        // Tạo ID nếu chưa có
        if (!isset($data['id'])) {
            $data['id'] = uniqid(substr($table, 0, 1) . '_');
        }
        
        // Thêm timestamp
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        
        $all[] = $data;
        
        if ($this->write($table, $all)) {
            return $data;
        }
        
        return null;
    }

    /**
     * Cập nhật record
     */
    public function update($table, $id, $data) {
        $all = $this->read($table);
        
        foreach ($all as $key => $record) {
            if ($record['id'] === $id) {
                $all[$key] = array_merge($record, $data);
                $all[$key]['updated_at'] = date('Y-m-d H:i:s');
                
                if ($this->write($table, $all)) {
                    return $all[$key];
                }
                return null;
            }
        }
        
        return null;
    }

    /**
     * Xoá record
     */
    public function delete($table, $id) {
        $all = $this->read($table);
        
        foreach ($all as $key => $record) {
            if ($record['id'] === $id) {
                unset($all[$key]);
                return $this->write($table, array_values($all));
            }
        }
        
        return false;
    }

    /**
     * Lấy tất cả records
     */
    public function getAll($table) {
        return $this->read($table);
    }

    /**
     * Đếm records
     */
    public function count($table) {
        $data = $this->read($table);
        return count($data);
    }

    /**
     * Lọc records
     */
    public function filter($table, $condition) {
        $data = $this->read($table);
        $result = [];
        
        foreach ($data as $record) {
            if ($condition($record)) {
                $result[] = $record;
            }
        }
        
        return $result;
    }
}

// Khởi động database
$db = Database::getInstance();
?>
