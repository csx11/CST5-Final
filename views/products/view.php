<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/ProductController.php';

$pageTitle  = 'Product Detail';
$activePage = 'products';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /views/products/index.php?error=Invalid+product+ID.');
    exit();
}

$productCtrl = new ProductController($conn);
$product     = $productCtrl->getById($id);

if (!$product) {
    header('Location: /views/products/index.php?error=Product+not+found.');
    exit();
}

$pageTitle = h($product->name);
?>
<?php require __DIR__ . '/../partial/header_app.php'; ?>

<div class="page-header">
    <div>
        <h1><?= h($product->name) ?></h1>
        <p style="font-family:var(--font-mono); font-size:.85rem;"><?= h($product->sku) ?></p>
    </div>
    <div style="display:flex; gap:.5rem;">
        <a href="/views/products/edit.php?id=<?= $product->id ?>" class="btn btn-warning">
            <i class="ph-bold ph-pencil"></i> Edit
        </a>
        <a href="/views/products/index.php" class="btn btn-outline">
            <i class="ph-bold ph-arrow-left"></i> Back
        </a>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 280px; gap:1rem; align-items:start;">

    <!-- Main info card -->
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="ph-bold ph-info"></i> Product Details</span>
            <span class="badge <?= $product->stockClass() ?>"><?= $product->stockStatus() ?></span>
        </div>
        <div class="card-body">

            <table style="width:100%; font-size:.9rem;">
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); width:40%; font-weight:600; border-bottom:1px solid var(--border);">Product Name</td>
                    <td style="padding:.6rem 0; border-bottom:1px solid var(--border);"><?= h($product->name) ?></td>
                </tr>
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">SKU</td>
                    <td style="padding:.6rem 0; border-bottom:1px solid var(--border); font-family:var(--font-mono);"><?= h($product->sku) ?></td>
                </tr>
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">Category</td>
                    <td style="padding:.6rem 0; border-bottom:1px solid var(--border);">
                        <?php if ($product->category_name): ?>
                            <span class="badge badge-info"><?= h($product->category_name) ?></span>
                        <?php else: ?>
                            <span class="text-muted">Uncategorized</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">Unit Price</td>
                    <td style="padding:.6rem 0; border-bottom:1px solid var(--border); font-weight:700; font-size:1.1rem;">
                        ₱<?= number_format($product->price, 2) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">Quantity</td>
                    <td style="padding:.6rem 0; border-bottom:1px solid var(--border);"><?= number_format($product->quantity) ?> units</td>
                </tr>
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); font-weight:600; border-bottom:1px solid var(--border);">Total Value</td>
                    <td style="padding:.6rem 0; border-bottom:1px solid var(--border); font-weight:600;">
                        ₱<?= number_format($product->price * $product->quantity, 2) ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding:.6rem 0; color:var(--text-muted); font-weight:600;">Description</td>
                    <td style="padding:.6rem 0;">
                        <?= !empty($product->description)
                              ? nl2br(h($product->description))
                              : '<span class="text-muted">No description provided.</span>' ?>
                    </td>
                </tr>
            </table>

        </div>
    </div>

    <!-- Side panel: timestamps + actions -->
    <div style="display:flex; flex-direction:column; gap:1rem;">

        <div class="card card-body">
            <h3 style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:.75rem;">Timestamps</h3>

            <div style="font-size:.85rem; line-height:2;">
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Created</span>
                    <span><?= date('M d, Y', strtotime($product->created_at)) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Updated</span>
                    <span><?= date('M d, Y', strtotime($product->updated_at)) ?></span>
                </div>
            </div>
        </div>

        <div class="card card-body">
            <h3 style="font-size:.8rem; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:.75rem;">Actions</h3>

            <div style="display:flex; flex-direction:column; gap:.5rem;">
                <a href="/views/products/edit.php?id=<?= $product->id ?>" class="btn btn-primary btn-full">
                    <i class="ph-bold ph-pencil"></i> Edit Product
                </a>

                <!-- Delete -->
                <form method="POST" action="/views/products/index.php"
                      onsubmit="return confirm('Delete this product? This cannot be undone.')">
                    <input type="hidden" name="delete_id" value="<?= $product->id ?>">
                    <button type="submit" class="btn btn-danger btn-full">
                        <i class="ph-bold ph-trash"></i> Delete Product
                    </button>
                </form>

                <a href="/views/products/index.php" class="btn btn-outline btn-full">
                    <i class="ph-bold ph-list"></i> All Products
                </a>
            </div>
        </div>

        <!-- Stock indicator -->
        <div class="card card-body" style="background:<?= $product->quantity === 0 ? 'var(--danger-bg)' : ($product->quantity <= 10 ? 'var(--warning-bg)' : 'var(--success-bg)') ?>; border-color:transparent;">
            <div style="text-align:center;">
                <div style="font-size:2rem; margin-bottom:.25rem;">
                    <?= $product->quantity === 0 ? '❌' : ($product->quantity <= 10 ? '⚠️' : '✅') ?>
                </div>
                <div style="font-weight:700; font-size:1rem;">
                    <?= $product->stockStatus() ?>
                </div>
                <div style="font-size:.8rem; margin-top:.25rem;">
                    <?= number_format($product->quantity) ?> unit(s) in stock
                </div>
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../partial/footer_app.php'; ?>
