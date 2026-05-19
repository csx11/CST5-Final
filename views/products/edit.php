<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$pageTitle  = 'Edit Product';
$activePage = 'products';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: /views/products/index.php?error=Invalid+product+ID.');
    exit();
}

$productCtrl  = new ProductController($conn);
$categoryCtrl = new CategoryController($conn);
$categories   = $categoryCtrl->getAll();


$product = $productCtrl->getById($id);
if (!$product) {
    header('Location: /views/products/index.php?error=Product+not+found.');
    exit();
}

$errors = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $data = [
        'name'        => $_POST['name']        ?? '',
        'sku'         => $_POST['sku']         ?? '',
        'price'       => $_POST['price']        ?? '',
        'quantity'    => $_POST['quantity']     ?? '',
        'description' => $_POST['description'] ?? '',
        'category_id' => $_POST['category_id'] ?? null,
    ];

    $result = $productCtrl->update($id, $data);

    if ($result['success']) {
        header('Location: /views/products/index.php?success=' . urlencode($result['message']));
        exit();
    } else {
        $errors = $result['message'];
        
        $product->name        = $data['name'];
        $product->sku         = $data['sku'];
        $product->price       = (float)$data['price'];
        $product->quantity    = (int)$data['quantity'];
        $product->description = $data['description'];
        $product->category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
    }
}
?>
<?php require __DIR__ . '/../partial/header_app.php'; ?>

<div class="page-header">
    <div>
        <h1>Edit Product</h1>
        <p>Update the details for <strong><?= h($product->name) ?></strong>.</p>
    </div>
    <div style="display:flex; gap:.5rem;">
        <a href="/views/products/view.php?id=<?= $product->id ?>" class="btn btn-outline">
            <i class="ph-bold ph-eye"></i> View
        </a>
        <a href="/views/products/index.php" class="btn btn-outline">
            <i class="ph-bold ph-arrow-left"></i> Back
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <i class="ph-bold ph-warning-circle"></i>
        <?= htmlspecialchars($errors, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div style="max-width:720px;">
<div class="card">
    <div class="card-header">
        <span class="card-title">
            <i class="ph-bold ph-pencil"></i> Editing: <?= h($product->name) ?>
        </span>
        <span class="badge badge-neutral" style="font-family:var(--font-mono);"><?= h($product->sku) ?></span>
    </div>
    <div class="card-body">

        <form method="POST" novalidate>

            <div class="form-row">
                <div class="form-group">
                    <label for="name" class="required">Product Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= h($product->name) ?>"
                        required
                        maxlength="150"
                    >
                </div>

                <div class="form-group">
                    <label for="sku" class="required">SKU</label>
                    <input
                        type="text"
                        id="sku"
                        name="sku"
                        value="<?= h($product->sku) ?>"
                        required
                        maxlength="80"
                        style="font-family: var(--font-mono); text-transform:uppercase;"
                        oninput="this.value = this.value.toUpperCase()"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price" class="required">Price (₱)</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="<?= number_format($product->price, 2, '.', '') ?>"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="quantity" class="required">Quantity</label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        value="<?= $product->quantity ?>"
                        min="0"
                        step="1"
                        required
                    >
                    <p class="form-hint">
                        Current status:
                        <span class="badge <?= $product->stockClass() ?>"><?= $product->stockStatus() ?></span>
                    </p>
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">— No Category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>"
                            <?= $product->category_id === $cat->id ? 'selected' : '' ?>>
                            <?= h($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3">
                    <?= h($product->description) ?>
                </textarea>
            </div>

            <hr class="divider">

            <div style="display:flex; gap:.75rem; justify-content:space-between; align-items:center; flex-wrap:wrap;">
                <small class="text-muted">
                    Last updated: <?= date('M d, Y g:i A', strtotime($product->updated_at)) ?>
                </small>
                <div style="display:flex; gap:.75rem;">
                    <a href="/views/products/index.php" class="btn btn-outline">Cancel</a>
                    <button type="submit" name="update_product" class="btn btn-primary">
                        <i class="ph-bold ph-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
</div>

<?php require __DIR__ . '/../partial/footer_app.php'; ?>
