<?php
require_once __DIR__ . '/../config/database.php';

class Task {
    
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllTasks($status = null) {
        $query = "SELECT * FROM tasks";
        $params = [];

        if ($status) {
            $query .= " WHERE status = :status";
            $params[':status'] = $status;
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getTaskById($id) {
        $query = "SELECT * FROM tasks WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function createTask($data) {
        $query = "INSERT INTO tasks (title, description, status) VALUES (:title, :description, :status)";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':status', $data['status']);
        
        return $stmt->execute() ? $this->db->lastInsertId() : false;
    }

    public function updateTask($id, $data) {
        $query = "UPDATE tasks SET 
                    title = :title, 
                    description = :description, 
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";
        
        $stmt = $this->db->prepare($query);
        
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function validateTaskData($data) {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }
        
        if (empty($data['status'])) {
            $errors['status'] = 'Status is required';
        } elseif (!in_array($data['status'], ['pending', 'in-progress', 'completed'])) {
            $errors['status'] = 'Status must be one of: pending, in-progress, completed';
        }
        
        return empty($errors) ? true : $errors;
    }
}
