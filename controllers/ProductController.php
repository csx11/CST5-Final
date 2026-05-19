<?php

require_once __DIR__ . '/../models/Product.php';

class ProductController {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    
    
    public function getAll(string $search = '', int $category_id = 0): array {
        $sql = 'SELECT p.*, c.name AS category_name
                  FROM products p
                  LEFT JOIN categories c ON p.category_id = c.id
                 WHERE 1=1';

        $params = [];
        $types  = '';

        if (!empty($search)) {
            $like      = '%' . $search . '%';
            $sql      .= ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)';
            $params[]  = $like;
            $params[]  = $like;
            $params[]  = $like;
            $types    .= 'sss';
        }

        if ($category_id > 0) {
            $sql      .= ' AND p.category_id = ?';
            $params[]  = $category_id;
            $types    .= 'i';
        }

        $sql .= ' ORDER BY p.updated_at DESC';

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = new Product(
                $row['name'],
                $row['sku'],
                (float)$row['price'],
                (int)$row['quantity'],
                $row['description'] ?? '',
                $row['category_id'] ? (int)$row['category_id'] : null,
                $row['created_by']  ? (int)$row['created_by']  : null,
                (int)$row['id'],
                $row['created_at'],
                $row['updated_at'],
                $row['category_name'] ?? ''
            );
        }
        $stmt->close();
        return $products;
    }

    
    public function getById(int $id): ?Product {
        $stmt = $this->conn->prepare(
            'SELECT p.*, c.name AS category_name
               FROM products p
               LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.id = ?
              LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return null;
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        return new Product(
            $row['name'],
            $row['sku'],
            (float)$row['price'],
            (int)$row['quantity'],
            $row['description'] ?? '',
            $row['category_id'] ? (int)$row['category_id'] : null,
            $row['created_by']  ? (int)$row['created_by']  : null,
            (int)$row['id'],
            $row['created_at'],
            $row['updated_at'],
            $row['category_name'] ?? ''
        );
    }

    
    
    public function create(array $data): array {
        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        
        if ($this->skuExists($data['sku'])) {
            return ['success' => false, 'message' => 'SKU already exists. Use a unique SKU.'];
        }

        $name        = trim($data['name']);
        $sku         = strtoupper(trim($data['sku']));
        $price       = (float)$data['price'];
        $quantity    = (int)$data['quantity'];
        $description = trim($data['description'] ?? '');
        $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $created_by  = !empty($data['created_by'])  ? (int)$data['created_by']  : null;

        $stmt = $this->conn->prepare(
            'INSERT INTO products
                (name, sku, category_id, price, quantity, description, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssidisi',
            $name, $sku, $category_id, $price, $quantity, $description, $created_by
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Product added successfully.'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => "Failed to add product: $error"];
    }

    
    public function update(int $id, array $data): array {
        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(' ', $errors)];
        }

        
        if ($this->skuExists($data['sku'], $id)) {
            return ['success' => false, 'message' => 'SKU already used by another product.'];
        }

        $name        = trim($data['name']);
        $sku         = strtoupper(trim($data['sku']));
        $price       = (float)$data['price'];
        $quantity    = (int)$data['quantity'];
        $description = trim($data['description'] ?? '');
        $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;

        $stmt = $this->conn->prepare(
            'UPDATE products
                SET name=?, sku=?, category_id=?, price=?, quantity=?, description=?
              WHERE id=?'
        );
        $stmt->bind_param('ssidisi',
            $name, $sku, $category_id, $price, $quantity, $description, $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Product updated successfully.'];
        }

        $error = $stmt->error;
        $stmt->close();
        return ['success' => false, 'message' => "Failed to update product: $error"];
    }

    
    public function delete(int $id): array {
        $stmt = $this->conn->prepare('DELETE FROM products WHERE id = ?');
        $stmt->bind_param('i', $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();
            return ['success' => true, 'message' => 'Product deleted.'];
        }

        $stmt->close();
        return ['success' => false, 'message' => 'Product not found or could not be deleted.'];
    }

    
    
    public function getSummary(): array {
        $result = $this->conn->query(
            'SELECT
                COUNT(*)                                    AS total_products,
                SUM(quantity)                               AS total_items,
                SUM(price * quantity)                       AS total_value,
                SUM(quantity = 0)                           AS out_of_stock,
                SUM(quantity > 0 AND quantity <= 10)        AS low_stock
             FROM products'
        );
        return $result->fetch_assoc() ?? [];
    }

    
    
    private function validateProduct(array $data): array {
        $errors = [];

        if (empty(trim($data['name'] ?? '')))
            $errors[] = 'Product name is required.';

        if (empty(trim($data['sku'] ?? '')))
            $errors[] = 'SKU is required.';

        if (!isset($data['price']) || !is_numeric($data['price']) || $data['price'] < 0)
            $errors[] = 'Price must be a non-negative number.';

        if (!isset($data['quantity']) || !is_numeric($data['quantity']) || $data['quantity'] < 0)
            $errors[] = 'Quantity must be a non-negative number.';

        return $errors;
    }

    
    private function skuExists(string $sku, int $excludeId = 0): bool {
        $sku  = strtoupper(trim($sku));
        $stmt = $this->conn->prepare(
            'SELECT id FROM products WHERE sku = ? AND id != ? LIMIT 1'
        );
        $stmt->bind_param('si', $sku, $excludeId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }
}
