<?php

require_once __DIR__ . '/../models/Category.php';

class CategoryController {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    
    public function getAll(): array {
        $result     = $this->conn->query('SELECT * FROM categories ORDER BY name');
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = new Category(
                $row['name'],
                $row['description'] ?? '',
                (int)$row['id'],
                $row['created_at']
            );
        }
        return $categories;
    }
}
