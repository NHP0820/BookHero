<?php
include '../../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id && $user_role != 'admin') {
    temp('info', 'Please login first');
    redirect("../staffLogin.php");
    exit;
}

// ----------------------------------------------------------------------------

// Get all categories
$categories = $_db->query('SELECT * FROM category ORDER BY name')->fetchAll();

if (is_post()) {
    $productName = req('name');
    $author = req('author');
    $description = req('description');
    $price = req('price');
    $stockQuantity = req('stock_quantity');
    $selectedCategories = req('categories') ?: [];
    $f = get_file('product_photo');

    // Validate: name
    if ($productName == '') {
        $_err['name'] = 'Required';
    }
    else if (strlen($productName) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

    // Validate: author
    if ($author == '') {
        $_err['author'] = 'Required';
    }
    else if (strlen($author) > 100) {
        $_err['author'] = 'Maximum 100 characters';
    }

    // Validate: description
    if (strlen($description) > 500) {
        $_err['description'] = 'Maximum 500 characters';
    }

    // Validate: price
    if ($price == '') {
        $_err['price'] = 'Required';
    }
    else if (!is_money($price)) {
        $_err['price'] = 'Must be money';
    }
    else if ($price < 0.01 || $price > 99.99) {
        $_err['price'] = 'Must between 0.01 - 99.99';
    }

    // Validate: stock_quantity
    if ($stockQuantity == '') {
        $_err['stock_quantity'] = 'Required';
    }
    else if (!is_numeric($stockQuantity)) {
        $_err['stock_quantity'] = 'Must be integer';
    }
    else if ($stockQuantity < 1 || $stockQuantity > 100) {
        $_err['stock_quantity'] = 'Must between 1 - 100';
    }

    // Validate: categories
    if (empty($selectedCategories)) {
        $_err['categories'] = 'Please select at least one category';
    }

    // Validate: photo
    if (!$f) {
        $_err['product_photo'] = 'Required';
    }
    else if (!str_starts_with($f->type, 'image/')) {
        $_err['product_photo'] = 'Must be image';
    }
    else if ($f->size > 1 * 1024 * 1024) {
        $_err['product_photo'] = 'Maximum 1MB';
    }

    // DB operation
    if (!$_err) {
        $_db->beginTransaction();
        
        try {
            // Insert product
            $stm = $_db->prepare('
                INSERT INTO product (name, author, description, price, stock_quantity)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stm->execute([$productName, $author, $description, $price, $stockQuantity]);
            $productId = $_db->lastInsertId();

            // Insert category relationships
            foreach ($selectedCategories as $categoryId) {
                $stm = $_db->prepare('
                    INSERT INTO category_product (category_id, product_id)
                    VALUES (?, ?)
                ');
                $stm->execute([$categoryId, $productId]);
            }

            // Save photo
            $productPhoto = save_photo($f, '../../images');
            
            // Insert photo record
            $stm = $_db->prepare('
                INSERT INTO product_photo (product_photo, product_id)
                VALUES (?, ?)
            ');
            $stm->execute([$productPhoto, $productId]);

            $_db->commit();
            
            temp('info', 'Product inserted successfully');
            redirect('index.php');
        } catch (Exception $ex) {
            $_db->rollBack();
            error_log("Insert Error: " . $ex->getMessage());
            $_err[] = 'Failed to insert product. Please try again. Error: ' . $ex->getMessage();
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Product | Insert';
include 'C:\xampp\htdocs\dashboard\bookHero\app\_staffHead.php';
?>

<style>
.btnp {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 15px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    transition: background 0.3s ease;
}
</style>


<p>
    <button class="btnp" data-get="index.php">Back to Products</button>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="name">Product Name</label>
    <?= html_text('name', 'maxlength="100"') ?>
    <?= err('name') ?>

    <label for="author">Author</label>
    <?= html_text('author', 'maxlength="100"') ?>
    <?= err('author') ?>

    <label for="description">Description</label>
    <textarea id="description" name="description" maxlength="500" rows="3"><?= encode($description ?? '') ?></textarea>
    <?= err('description') ?>

    <label for="price">Price (RM)</label>
    <?= html_number('price', 0.01, 99.99, 0.01) ?>
    <?= err('price') ?>

    <label for="stock_quantity">Stock Quantity</label>
    <?= html_number('stock_quantity', 1, 100, 1) ?>
    <?= err('stock_quantity') ?>

    <label for="categories">Categories</label>
    <select id="categories" name="categories[]" multiple="multiple" class="form-control">
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c->category ?>"><?= $c->name ?></option>
        <?php endforeach ?>
    </select>
    <?= err('categories') ?>
    <small class="text-muted">Select multiple categories</small>

    <label for="product_photo">Product Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('product_photo', 'image/*', 'hidden') ?>
        <img src="/images/photo.jpg">
    </label>
    <?= err('product_photo') ?>

    <section>
        <button class="btnp">Submit</button>
        <button class="btnp" type="reset">Reset</button>
    </section>
</form>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#categories').select2({
        placeholder: "Select categories",
        width: '100%'
    });
});
</script>

<?php
include '../../_foot.php';