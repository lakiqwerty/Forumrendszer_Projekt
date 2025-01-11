<?php
namespace models;

use PDO;
use PDOException;

class Database
{
    private $host = 'localhost';
    private $db_name = 'forum';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function __construct()
    {
        $this->conn = null;
        try {
            // PDO kapcsolat létrehozása
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            // Hibakezelés beállítása
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // Hibás kapcsolat esetén
            echo "Kapcsolódási hiba: " . $e->getMessage();
        }
    }

    // Visszaadja az adatbázis kapcsolatot
    public function getConnection()
    {
        return $this->conn;
    }
}
?>
