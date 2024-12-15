<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $role_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($username, $password, $email) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $default_role_id = 2;

        $query = "INSERT INTO users (username, password, email, role_id) VALUES (:username, :password, :email, :role_id)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role_id', $default_role_id);

        return $stmt->execute();
    }




    public function login($username, $password) {
        $query = "SELECT id, password FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user['id']; // Sikeres bejelentkezés
        } else {
            return false; // Sikertelen bejelentkezés
        }
    }

    public function getUserById($userId) {
        $query = "SELECT u.username, u.email, r.name AS role_name
              FROM users u
              LEFT JOIN roles r ON u.role_id = r.id
              WHERE u.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


}
?>