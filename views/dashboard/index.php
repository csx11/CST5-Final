<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/auth.php';       
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

$productCtrl  = new ProductController($conn);
$categoryCtrl = new CategoryController($conn);


$summary    = $productCtrl->getSummary();


$lowStock   = array_filter(
    $productCtrl->getAll(),
    fn($p) => $p->quantity > 0 && $p->quantity <= 10
);


$outOfStock = array_filter(
    $productCtrl->getAll(),
    fn($p) => $p->quantity === 0
);


function peso(float $amount): string {
    return '₱ ' . number_format($amount, 2);
}
?>
<?php require __DIR__ . '/../partial/header_app.php'; ?>

<!-- ── Flash messages ─────────────────────────────────────────── -->
<?= flash('success') ?>
<?= flash('error') ?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?= h($currentUser['username']) ?>! Here's your inventory overview.</p>
    </div>
    <a href="/views/products/create.php" class="btn btn-primary">
        <i class="ph-bold ph-plus"></i> Add Product
    </a>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────────── -->
<div class="stat-grid">

    <div class="stat-card">
        <div class="stat-icon teal"><i class="ph-bold ph-package"></i></div>
        <div>
            <div class="stat-label">Total Products</div>
            <div class="stat-value"><?= number_format((int)($summary['total_products'] ?? 0)) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="ph-bold ph-stack"></i></div>
        <div>
            <div class="stat-label">Total Items</div>
            <div class="stat-value"><?= number_format((int)($summary['total_items'] ?? 0)) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon teal"><i class="ph-bold ph-currency-circle-dollar"></i></div>
        <div>
            <div class="stat-label">Inventory Value</div>
            <div class="stat-value" style="font-size:1.1rem;">
                <?= peso((float)($summary['total_value'] ?? 0)) ?>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon yellow"><i class="ph-bold ph-warning"></i></div>
        <div>
            <div class="stat-label">Low Stock</div>
            <div class="stat-value"><?= (int)($summary['low_stock'] ?? 0) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon red"><i class="ph-bold ph-x-circle"></i></div>
        <div>
            <div class="stat-label">Out of Stock</div>
            <div class="stat-value"><?= (int)($summary['out_of_stock'] ?? 0) ?></div>
        </div>
    </div>

</div>

<!-- ── Alerts section ─────────────────────────────────────────── -->
<?php if (!empty($outOfStock)): ?>
<div class="alert alert-danger mb-2">
    <i class="ph-bold ph-warning"></i>
    <strong><?= count($outOfStock) ?> product(s) are out of stock.</strong>
    <a href="/views/products/index.php" style="color:inherit; margin-left:.5rem;">View →</a>
</div>
<?php endif; ?>

<?php if (!empty($lowStock)): ?>
<div class="alert alert-warning mb-2">
    <i class="ph-bold ph-warning-circle"></i>
    <strong><?= count($lowStock) ?> product(s) are running low.</strong>
    <a href="/views/products/index.php" style="color:inherit; margin-left:.5rem;">View →</a>
</div>
<?php endif; ?>

<!-- ── Two-column layout: low stock + quick links ─────────────── -->
<div style="display:grid; grid-template-columns: 1fr 300px; gap:1rem; align-items:start; flex-wrap:wrap;">

    <!-- Low Stock Table -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="ph-bold ph-warning" style="color:var(--warning)"></i> Low / Out-of-Stock Products</span>
            <a href="/views/products/index.php" class="btn btn-outline btn-sm">View All</a>
        </div>

        <?php
        $watchlist = array_merge(
            array_values($outOfStock),
            array_values($lowStock)
        );
        ?>

        <?php if (empty($watchlist)): ?>
            <div class="empty-state" style="padding:2rem;">
                <h3>All stocked up!</h3>
                <p>No products need attention right now.</p>
            </div>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($watchlist as $p): ?>
                    <tr>
                        <td class="fw-600"><?= h($p->name) ?></td>
                        <td class="sku-cell"><?= h($p->sku) ?></td>
                        <td><?= h($p->category_name ?: '—') ?></td>
                        <td><?= $p->quantity ?></td>
                        <td><span class="badge <?= $p->stockClass() ?>"><?= $p->stockStatus() ?></span></td>
                        <td>
                            <a href="/views/products/edit.php?id=<?= $p->id ?>"
                               class="btn btn-outline btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div style="display:flex; flex-direction:column; gap:1rem;">

        <div class="card card-body">
            <h3 style="font-size:.9rem; margin-bottom:.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em;">By Category</h3>
            <?php
            $categories = $categoryCtrl->getAll();
            foreach ($categories as $cat):
                $count = count(array_filter(
                    $productCtrl->getAll('', $cat->id),
                    fn($p) => true
                ));
            ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:.4rem 0; border-bottom:1px solid var(--border); font-size:.85rem;">
                <span><?= h($cat->name) ?></span>
                <span class="badge badge-neutral"><?= $count ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../partial/footer_app.php'; ?>
