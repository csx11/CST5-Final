<?php

class User {
    public int    $id;
    public string $username;
    public string $email;
    public string $password;   
    public string $role;
    public string $created_at;

    public function __construct(
        string $username,
        string $email,
        string $password,
        string $role       = 'staff',
        int    $id         = 0,
        string $created_at = ''
    ) {
        $this->id         = $id;
        $this->username   = trim($username);
        $this->email      = trim($email);
        $this->password   = $password;
        $this->role       = $role;
        $this->created_at = $created_at;
    }

    
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
}
