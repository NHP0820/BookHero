<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

// Get all categories
$categories = $_db->query('SELECT * FROM category ORDER BY name')->fetchAll();

if (is_get()) {
    $id = req('id');

    $stm = $_db->prepare('
        SELECT p.*, pp.product_photo 
        FROM product p
        LEFT JOIN product_photo pp ON p.product_id = pp.product_id
        WHERE p.product_id = ?
    ');
    $stm->execute([$id]);
    $p = $stm->fetch();

    if (!$p) {
        redirect('index.php');
    }

    // Get current categories using category_id
    $stm = $_db->prepare('
        SELECT category_id FROM category_product
        WHERE product_id = ?
    ');
    $stm->execute([$id]);
    $currentCategories = $stm->fetchAll(PDO::FETCH_COLUMN);

    extract((array)$p);
    $_SESSION['photo'] = $p->product_photo;
}

if (is_post()) {
    $id = req('id');
    $productName = req('name');
    $author = req('author');
    $description = req('description');
    $price = req('price');
    $stockQuantity = req('stock_quantity');
    $selectedCategories = req('categories') ?: [];
    $f = get_file('product_photo');
    $productPhoto = $_SESSION['photo'];

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

    // Validate: photo (only if new file uploaded)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['product_photo'] = 'Must be image';
        }
        else if ($f->size > 1 * 1024 * 1024) {
            $_err['product_photo'] = 'Maximum 1MB';
        }
    }

    // DB operation
    if (!$_err) {
        $_db->beginTransaction();
        
        try {
            // Update product
            $stm = $_db->prepare('
                UPDATE product
                SET name = ?, author = ?, description = ?, price = ?, stock_quantity = ?
                WHERE product_id = ?
            ');
            $stm->execute([$productName, $author, $description, $price, $stockQuantity, $id]);

            // Update categories - first remove existing
            $stm = $_db->prepare('DELETE FROM category_product WHERE product_id = ?');
            $stm->execute([$id]);
            
            // Then add new selections using category_id
            foreach ($selectedCategories as $categoryId) {
                $stm = $_db->prepare('
                    INSERT INTO category_product (category_id, product_id)
                    VALUES (?, ?)
                ');
                $stm->execute([$categoryId, $id]);
            }

            // Update photo if new file uploaded
            if ($f) {
                // Delete old photo
                if ($productPhoto && file_exists("../photos/$productPhoto")) {
                    unlink("../photos/$productPhoto");
                }
                
                // Save new photo
                $productPhoto = save_photo($f, '../photos');
                
                // Update photo record
                $stm = $_db->prepare('
                    UPDATE product_photo
                    SET product_photo = ?
                    WHERE product_id = ?
                ');
                $stm->execute([$productPhoto, $id]);
                
                // If no photo record exists, insert new one
                if ($stm->rowCount() === 0) {
                    $stm = $_db->prepare('
                        INSERT INTO product_photo (product_photo, product_id)
                        VALUES (?, ?)
                    ');
                    $stm->execute([$productPhoto, $id]);
                }
            }

            $_db->commit();
            
            temp('info', 'Product updated successfully');
            redirect('index.php');
        } catch (Exception $ex) {
            $_db->rollBack();
            $_err[] = 'Failed to update product. Please try again.';
            error_log("Update error: " . $ex->getMessage());
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Product | Update';
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
    <input type="hidden" name="id" value="<?= $id ?>">

    <label for="name">Product Name</label>
    <?= html_text('name', 'maxlength="100" value="'.($name ?? '').'"') ?>
    <?= err('name') ?>

    <label for="author">Author</label>
    <?= html_text('author', 'maxlength="100" value="'.($author ?? '').'"') ?>
    <?= err('author') ?>

    <label for="description">Description</label>
    <textarea id="description" name="description" maxlength="500" rows="3"><?= encode($description ?? '') ?></textarea>
    <?= err('description') ?>

    <label for="price">Price (RM)</label>
    <?= html_number('price', 0.01, 99.99, 0.01, 'value="'.($price ?? '').'"') ?>
    <?= err('price') ?>

    <label for="stock_quantity">Stock Quantity</label>
    <?= html_number('stock_quantity', 1, 100, 1, 'value="'.($stock_quantity ?? '').'"') ?>
    <?= err('stock_quantity') ?>

    <label for="categories">Categories</label>
    <select id="categories" name="categories[]" multiple="multiple" class="form-control">
        <?php foreach ($categories as $c): ?>
            <option value="<?= $c->category ?>" 
                <?= in_array($c->category, $currentCategories ?? []) ? 'selected' : '' ?>>
                <?= $c->name ?>
            </option>
        <?php endforeach ?>
    </select>
    <?= err('categories') ?>
    <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>

    <label for="product_photo">Product Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('product_photo', 'image/*', 'hidden') ?>
        <img src="../../images/<?= $productPhoto ?? 'photo.jpg' ?>">
    </label>
    <?= err('product_photo') ?>

    <section>
        <button class="btnp" >Update</button>
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