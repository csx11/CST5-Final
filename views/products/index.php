<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$pageTitle  = 'Products';
$activePage = 'products';

$productCtrl  = new ProductController($conn);
$categoryCtrl = new CategoryController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $result   = $productCtrl->delete($deleteId);
    $param    = $result['success'] ? 'success' : 'error';
    header('Location: /views/products/index.php?' . $param . '=' . urlencode($result['message']));
    exit();
}

$search      = trim($_GET['search']      ?? '');
$category_id = (int)($_GET['category_id'] ?? 0);

$products   = $productCtrl->getAll($search, $category_id);
$categories = $categoryCtrl->getAll();
?>
<?php require __DIR__ . '/../partial/header_app.php'; ?>

<?= flash('success') ?>
<?= flash('error') ?>

<div class="page-header">
    <div>
        <h1>Products</h1>
        <p>Manage your inventory — <?= count($products) ?> product(s) found.</p>
    </div>
    <a href="/views/products/create.php" class="btn btn-primary">
        <i class="ph-bold ph-plus"></i> Add Product
    </a>
</div>

<div class="card mb-2">
    <div class="card-body" style="padding:.85rem 1.25rem;">
        <form method="GET" class="toolbar" id="filterForm">
            <div class="search-input" style="flex:1;">
                <i class="ph-bold ph-magnifying-glass search-icon"></i>
                <input type="text" name="search" placeholder="Search by name, SKU, or description…"
                    value="<?= h($search) ?>" oninput="this.form.submit()">
            </div>
            <select name="category_id" onchange="this.form.submit()" style="width:auto;">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat->id ?>" <?= $category_id === $cat->id ? 'selected' : '' ?>>
                        <?= h($cat->name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($search) || $category_id > 0): ?>
                <a href="/views/products/index.php" class="btn btn-outline btn-sm">
                    <i class="ph-bold ph-x"></i> Clear
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h3>No products found</h3>
            <p>
                <?php if (!empty($search) || $category_id > 0): ?>
                    Try adjusting your search or filter. <a href="/views/products/index.php">Clear filters</a>
                <?php else: ?>
                    Get started by <a href="/views/products/create.php">adding a product</a>.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Product Name</th><th>SKU</th><th>Category</th>
                    <th>Price</th><th>Qty</th><th>Status</th><th>Last Updated</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $i => $p): ?>
                <tr>
                    <td class="text-muted" style="font-size:.8rem;"><?= $i + 1 ?></td>
                    <td>
                        <span class="fw-600"><?= h($p->name) ?></span>
                        <?php if (!empty($p->description)): ?>
                            <br><small class="text-muted" style="font-size:.75rem;">
                                <?= h(mb_strimwidth($p->description, 0, 60, '…')) ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td class="sku-cell"><?= h($p->sku) ?></td>
                    <td>
                        <?php if ($p->category_name): ?>
                            <span class="badge badge-info"><?= h($p->category_name) ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="fw-600">₱<?= number_format($p->price, 2) ?></td>
                    <td><?= number_format($p->quantity) ?></td>
                    <td><span class="badge <?= $p->stockClass() ?>"><?= $p->stockStatus() ?></span></td>
                    <td class="text-muted" style="font-size:.8rem; white-space:nowrap;">
                        <?= date('M d, Y', strtotime($p->updated_at)) ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:.4rem;">
                            <a href="/views/products/view.php?id=<?= $p->id ?>" class="btn btn-outline btn-sm" title="View">
                                <i class="ph-bold ph-eye"></i>
                            </a>
                            <a href="/views/products/edit.php?id=<?= $p->id ?>" class="btn btn-warning btn-sm" title="Edit">
                                <i class="ph-bold ph-pencil"></i>
                            </a>
                            <form method="POST" onsubmit="return confirmDelete('<?= h($p->name) ?>')" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $p->id ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                    <i class="ph-bold ph-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <span class="page-info">
            Showing <strong><?= count($products) ?></strong> product(s)
            <?= !empty($search) ? ' matching "<strong>' . h($search) . '</strong>"' : '' ?>
            <?= $category_id > 0 ? ' in selected category' : '' ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(name) {
    return confirm(`Are you sure you want to delete "${name}"?\nThis action cannot be undone.`);
}
</script>

<?php require __DIR__ . '/../partial/footer_app.php'; ?>
