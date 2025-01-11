<?php

namespace models;

use PDO;
use PDOException;

class User
{
    private $conn;
    private $table_name = "users";

    public $id;
    public $password;
    public $username;
    public $email;
    public $role_id;

    // Konstruktor, adatbázis kapcsolat beállítása
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Felhasználó regisztrálása
    public function register($username, $password, $email)
    {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $default_role_id = 2;

            // SQL lekérdezés a felhasználó hozzáadására
            $query = "INSERT INTO " . $this->table_name . " (username, password, email, role_id) 
                      VALUES (:username, :password, :email, :role_id)";
            $stmt = $this->conn->prepare($query);

            // Paraméterek kötése
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role_id', $default_role_id);

            return $stmt->execute();
        } catch (PDOException $e) {
            // Hibakezelés
            error_log("Regisztrációs hiba: " . $e->getMessage());
            return false; // Hibás végrehajtás
        }
    }

    // Felhasználó bejelentkezése
    public function login($username, $password)
    {
        try {
            // SQL lekérdezés a felhasználó keresésére
            $query = "SELECT id, password FROM " . $this->table_name . " WHERE username = :username";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Ellenőrzés, hogy a jelszó helyes-e
            if ($user && password_verify($password, $user['password'])) {
                return $user['id']; // Sikeres bejelentkezés
            } else {
                return false; // Sikertelen bejelentkezés
            }
        } catch (PDOException $e) {
            // Hibakezelés
            error_log("Bejelentkezési hiba: " . $e->getMessage());
            return false;
        }
    }

    // Felhasználó adatainak lekérdezése ID alapján
    public function getUserById($user_id)
    {
        try {
            // SQL lekérdezés a felhasználó adatainak lekérdezésére
            $query = "SELECT id, username, email, role_id FROM " . $this->table_name . " WHERE id = :user_id LIMIT 0,1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Ha találunk felhasználót, visszaadjuk az adatokat
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return $user;
        } catch (PDOException $e) {
            // Hibakezelés
            error_log("Felhasználó lekérdezési hiba: " . $e->getMessage());
            return false;
        }
    }
}
?>
