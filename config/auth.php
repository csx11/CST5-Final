<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /index.php?error=Please+log+in+to+continue.');
    exit();
}


$currentUser = [
    'id'       => $_SESSION['user_id'],
    'username' => $_SESSION['user_username'],
    'email'    => $_SESSION['user_email'],
    'role'     => $_SESSION['user_role'],
];


function requireAdmin(): void {
    if ($_SESSION['user_role'] !== 'admin') {
        header('Location: /views/dashboard/index.php?error=Access+denied.');
        exit();
    }
}


function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


function flash(string $type = 'success'): string {
    $key = $type === 'success' ? 'success' : 'error';
    if (!empty($_GET[$key])) {
        $cls = $type === 'success' ? 'alert-success' : 'alert-danger';
        $msg = htmlspecialchars($_GET[$key], ENT_QUOTES, 'UTF-8');
        return "<div class=\"alert $cls\">$msg</div>";
    }
    return '';
}
