<?php

require_once __DIR__ . '/../models/User.php';

class UserController {
    private mysqli $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    
    
    public function register(
        string $username,
        string $email,
        string $password,
        string $role = 'staff'
    ): array {
        
        $username = trim($username);
        $email    = trim($email);

        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        if (!in_array($role, ['admin', 'staff'])) {
            $role = 'staff';
        }

        
        $stmt = $this->conn->prepare(
            'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1'
        );
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Username or email already in use.'];
        }
        $stmt->close();

        
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            'INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('ssss', $username, $email, $hashed, $role);

        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Account created successfully.'];
        }

        $stmt->close();
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }

    
    
    public function login(string $username, string $password): array {
        $username = trim($username);

        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Please enter your credentials.'];
        }

        
        $stmt = $this->conn->prepare(
            'SELECT id, username, email, password, role
               FROM users
              WHERE username = ? OR email = ?
              LIMIT 1'
        );
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        $row = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($password, $row['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        
        $_SESSION['user_id']       = $row['id'];
        $_SESSION['user_username'] = $row['username'];
        $_SESSION['user_email']    = $row['email'];
        $_SESSION['user_role']     = $row['role'];

        return ['success' => true, 'message' => 'Login successful.'];
    }

    
    
    public function logout(): void {
        $_SESSION = [];
        session_destroy();
        header('Location: /index.php');
        exit();
    }
}
