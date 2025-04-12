// includes/db_operations.php
<?php
require_once __DIR__ . '/../config/db.php';

class DBOperations {
    protected $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    protected function executeQuery($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        return $stmt;
    }
}
?>