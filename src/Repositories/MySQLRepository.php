<?php
namespace App\Repository;

use App\Interfaces\StorageInterface;
use PDO;
use PDOException;

class MySQLRepository implements StorageInterface {
    
    /**
     * @var PDO The database connection object.
     */
    private $conn;

    /**
     * @var string The name of the table to interact with.
     */
    private $table = 'documents'; 

    public function __construct(string $server_name, string $db_name, string $user_name, string $password) {
        try {
            // ... (Constructor is correct)
            $this->conn = new PDO("mysql:host=$server_name;dbname=$db_name;charset=utf8mb4", $user_name, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // [ ]remember to remove echo
            echo "Connected successfully";         
        } catch(PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getAll(): array {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_NUM); 
    }

    public function create(array $data): void {
        // [ ] column names are changed to snake_case (e.g., category_type).
        // [ ] first column (document_id, the uniqid) is included, but the SQL auto-increment ID is NOT.
        $sql = "INSERT INTO {$this->table} (document_id, name, category_type, effective_date, expiry_date, perpetual, status, document_link)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute($data); 
    }

    public function update(int $id, array $data): void {        

        $sql = "UPDATE {$this->table} SET
                    document_id = ?,
                    name = ?,
                    category_type = ?,
                    effective_date = ?,
                    expiry_date = ?,
                    perpetual = ?,
                    status = ?,
                    document_link = ?
                WHERE id = ?"; 
        
        $stmt = $this->conn->prepare($sql);
        $updateData = $data;
        $updateData[] = $id;

        $stmt->execute($updateData);
    }

    public function delete(int $id): void {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
    }
}