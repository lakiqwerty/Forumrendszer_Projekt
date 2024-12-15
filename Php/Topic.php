<?php
class Topic {
    private $conn;
    private $table_name = "topics";

    public $id;
    public $user_id;
    public $category_id;
    public $title;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, category_id, title) 
                  VALUES (:user_id, :category_id, :title)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':title', $this->title);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getAllTopics() {
        $query = "SELECT * FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function getTopicsByCategory() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE category_id = :category_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->execute();

        return $stmt;
    }
}
?>
