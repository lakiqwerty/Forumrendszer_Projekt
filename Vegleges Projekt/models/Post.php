<?php
namespace models;

class Post
{
    private $conn;
    private $table_name = "posts";

    public $id;
    public $topic_id;
    public $user_id;
    public $content;
    public $created_at;
    public $status;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (topic_id, user_id, content, status) 
                  VALUES (:topic_id, :user_id, :content, :status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':topic_id', $this->topic_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':content', $this->content);
        $stmt->bindParam(':status', $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getPostsByTopic()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE topic_id = :topic_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':topic_id', $this->topic_id);
        $stmt->execute();

        return $stmt;
    }
}

?>
