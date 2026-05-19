<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();





require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/UserController.php';

$errors  = '';
$success = '';
$pageTitle = 'Create Account';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';
    $role     = $_POST['role']          ?? 'staff';

    
    if ($password !== $confirm) {
        $errors = 'Passwords do not match.';
    } else {
        $controller = new UserController($conn);
        $result     = $controller->register($username, $email, $password, $role);

        if ($result['success']) {
            header('Location: /index.php?success=' . urlencode($result['message']));
            exit();
        } else {
            $errors = $result['message'];
        }
    }
}
?>
<?php require __DIR__ . '/../partial/header_auth.php'; ?>

<div class="auth-card" style="max-width:480px;">

    <div class="auth-logo">
        <div class="logo-icon"><i class="ph-bold ph-package"></i></div>
        <span>InvManager</span>
    </div>

    <h2>Create an account</h2>
    <p class="subtitle">Fill in the details below to register</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="ph-bold ph-warning-circle"></i>
            <?= htmlspecialchars($errors, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>

        <div class="form-group">
            <label for="username" class="required">Username</label>
            <input
                type="text"
                id="username"
                name="username"
                placeholder="Choose a username"
                value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
                maxlength="50"
            >
        </div>

        <div class="form-group">
            <label for="email" class="required">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="you@example.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required
                maxlength="100"
            >
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password" class="required">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Min. 6 characters"
                    required
                    minlength="6"
                >
            </div>

            <div class="form-group">
                <label for="confirm" class="required">Confirm Password</label>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    placeholder="Re-enter password"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="staff" <?= ($_POST['role'] ?? '') === 'staff' ? 'selected' : '' ?>>Staff</option>
                <option value="admin" <?= ($_POST['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <p class="form-hint">Staff can manage products. Admins can also add users.</p>
        </div>

        <button type="submit" name="register" class="btn btn-primary btn-full btn-lg">
            <i class="ph-bold ph-user-plus"></i>
            Create Account
        </button>
    </form>

    <div class="auth-footer">
        Already have an account?
        <a href="/index.php">Sign in</a>
    </div>
</div>

<?php require __DIR__ . '/../partial/footer_auth.php'; ?>
