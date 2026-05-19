<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /views/dashboard/index.php');
    exit();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/UserController.php';

$errors  = '';
$success = '';
$pageTitle = 'Login';

if (!empty($_GET['error']))   $errors  = htmlspecialchars($_GET['error'],   ENT_QUOTES, 'UTF-8');
if (!empty($_GET['success'])) $success = htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $controller = new UserController($conn);
    $result     = $controller->login($username, $password);

    if ($result['success']) {
        header('Location: /views/dashboard/index.php');
        exit();
    } else {
        $errors = $result['message'];
    }
}
?>
<?php require 'views/partial/header_auth.php'; ?>

<div class="auth-card">

    <div class="auth-logo">
        <div class="logo-icon"><i class="ph-bold ph-package"></i></div>
        <span>InvManager</span>
    </div>

    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to your account to continue</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <i class="ph-bold ph-warning-circle"></i>
            <?= $errors ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <i class="ph-bold ph-check-circle"></i>
            <?= $success ?>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <div class="form-group">
            <label for="username" class="required">Username or Email</label>
            <input type="text" id="username" name="username"
                value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>

        <div class="form-group">
            <label for="password" class="required">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" name="login" class="btn btn-primary btn-full btn-lg">
            <i class="ph-bold ph-sign-in"></i> Sign In
        </button>
    </form>

    <div class="auth-footer">
        Don't have an account?
        <a href="/views/auth/register.php">Create one</a>
    </div>
</div>

<?php require 'views/partial/footer_auth.php'; ?>
