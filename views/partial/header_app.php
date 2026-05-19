<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Inventory Manager</title>
    <link rel="stylesheet" href="/public/styles.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<div class="app-shell">

<aside class="sidebar">
    <a href="/views/dashboard/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="ph-bold ph-package"></i></div>
        <div>
            <span>InvManager</span>
            <small>Inventory System</small>
        </div>
    </a>

    <div class="nav-section">Main</div>

    <a href="/views/dashboard/index.php"
       class="nav-item <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
        <i class="ph-bold ph-squares-four nav-icon"></i>
        Dashboard
    </a>

    <div class="nav-section">Inventory</div>

    <a href="/views/products/index.php"
       class="nav-item <?= ($activePage ?? '') === 'products' ? 'active' : '' ?>">
        <i class="ph-bold ph-package nav-icon"></i>
        Products
    </a>

    <a href="/views/products/create.php"
       class="nav-item <?= ($activePage ?? '') === 'product-create' ? 'active' : '' ?>">
        <i class="ph-bold ph-plus-circle nav-icon"></i>
        Add Product
    </a>

    <?php if ($currentUser['role'] === 'admin'): ?>
    <div class="nav-section">Admin</div>
    <a href="/views/auth/register.php"
       class="nav-item <?= ($activePage ?? '') === 'register' ? 'active' : '' ?>">
        <i class="ph-bold ph-user-plus nav-icon"></i>
        Add User
    </a>
    <?php endif; ?>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
            </div>
            <div>
                <div class="user-name"><?= h($currentUser['username']) ?></div>
                <div class="user-role"><?= h($currentUser['role']) ?></div>
            </div>
        </div>
        <a href="/views/auth/logout.php" class="btn-logout">
            <i class="ph-bold ph-sign-out"></i> Logout
        </a>
    </div>
</aside>

<header class="topbar">
    <span class="topbar-title"><?= isset($pageTitle) ? h($pageTitle) : 'Dashboard' ?></span>
    <div class="topbar-right">
        <span class="topbar-badge">
            <i class="ph-bold ph-user"></i>
            <?= h($currentUser['username']) ?>
        </span>
    </div>
</header>

<main class="main-content">
