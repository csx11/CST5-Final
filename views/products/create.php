<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/ProductController.php';
require_once __DIR__ . '/../../controllers/CategoryController.php';

$pageTitle  = 'Add Product';
$activePage = 'product-create';

$productCtrl  = new ProductController($conn);
$categoryCtrl = new CategoryController($conn);
$categories   = $categoryCtrl->getAll();

$errors  = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $data = [
        'name'        => $_POST['name']        ?? '',
        'sku'         => $_POST['sku']         ?? '',
        'price'       => $_POST['price']        ?? '',
        'quantity'    => $_POST['quantity']     ?? '',
        'description' => $_POST['description'] ?? '',
        'category_id' => $_POST['category_id'] ?? null,
        'created_by'  => $currentUser['id'],
    ];

    $result = $productCtrl->create($data);

    if ($result['success']) {
        header('Location: /views/products/index.php?success=' . urlencode($result['message']));
        exit();
    } else {
        $errors = $result['message'];
    }
}


function old(string $key, string $default = ''): string {
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<?php require __DIR__ . '/../partial/header_app.php'; ?>

<div class="page-header">
    <div>
        <p>Fill in the details to add a new product to your inventory.</p>
    </div>
    <a href="/views/products/index.php" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back to Products
    </a>
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
        <span class="card-title"><i class="ph-bold ph-plus-circle"></i> Product Information</span>
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
                        placeholder="e.g. Wireless Mouse"
                        value="<?= old('name') ?>"
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
                        placeholder="e.g. ELEC-001"
                        value="<?= old('sku') ?>"
                        required
                        maxlength="80"
                        style="font-family: var(--font-mono); text-transform:uppercase;"
                        oninput="this.value = this.value.toUpperCase()"
                    >
                    <p class="form-hint">Must be unique. Automatically converted to uppercase.</p>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price" class="required">Price (₱)</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        placeholder="0.00"
                        value="<?= old('price') ?>"
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
                        placeholder="0"
                        value="<?= old('quantity') ?>"
                        min="0"
                        step="1"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">— No Category —</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>"
                            <?= (string)($_POST['category_id'] ?? '') === (string)$cat->id ? 'selected' : '' ?>>
                            <?= h($cat->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Optional product description…"
                    rows="3"
                ><?= old('description') ?></textarea>
            </div>

            <hr class="divider">

            <div style="display:flex; gap:.75rem; justify-content:flex-end;">
                <a href="/views/products/index.php" class="btn btn-outline">Cancel</a>
                <button type="submit" name="create_product" class="btn btn-primary">
                    <i class="ph-bold ph-floppy-disk"></i> Save Product
                </button>
            </div>

        </form>
    </div>
</div>
</div>

<?php require __DIR__ . '/../partial/footer_app.php'; ?>
