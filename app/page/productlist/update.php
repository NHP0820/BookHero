<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

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

    extract((array)$p);
    $_SESSION['photo'] = $p->product_photo;
}

if (is_post()) {
    $id = req('id');
    $name = req('name');
    $description = req('description');
    $price = req('price');
    $stock_quantity = req('stock_quantity');
    $f = get_file('product_photo');
    $product_photo = $_SESSION['photo'];

    // Validate: name
    if ($name == '') {
        $_err['name'] = 'Required';
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';
    }

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
    if ($stock_quantity == '') {
        $_err['stock_quantity'] = 'Required';
    }
    else if (!is_numeric($stock_quantity)) {
        $_err['stock_quantity'] = 'Must be integer';
    }
    else if ($stock_quantity < 1 || $stock_quantity > 100) {
        $_err['stock_quantity'] = 'Must between 1 - 100';
    }

    // Validate: photo (file)
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
            // Update photo if a new one was uploaded
            if ($f) {
                // Delete old photo
                if ($product_photo) {
                    @unlink("../../images/$product_photo");
                }
                
                // Save new photo
                $product_photo = save_photo($f, '../../images');
                
                // Update photo record
                $stm = $_db->prepare('
                    UPDATE product_photo 
                    SET product_photo = ?
                    WHERE product_id = ?
                ');
                $stm->execute([$product_photo, $id]);
                
                // If no rows affected, insert new photo record
                if ($stm->rowCount() === 0) {
                    $stm = $_db->prepare('
                        INSERT INTO product_photo (product_photo, product_id)
                        VALUES (?, ?)
                    ');
                    $stm->execute([$product_photo, $id]);
                }
            }

            // Update product
            $stm = $_db->prepare('
                UPDATE product
                SET name = ?, description = ?, price = ?, stock_quantity = ?
                WHERE product_id = ?
            ');
            $stm->execute([$name, $description, $price, $stock_quantity, $id]);

            $_db->commit();
            
            temp('info', 'Record updated');
            redirect('index.php');
        } catch (Exception $ex) {
            $_db->rollBack();
            $_err[] = 'Failed to update record';
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'Product | Update';
include '../../_staffHead.php';
?>

<p>
    <button data-get="index.php">Index</button>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="id" value="<?= $id ?>">

    <label for="name">Name</label>
    <?= html_text('name', 'maxlength="100"') ?>
    <?= err('name') ?>

    <label for="description">Description</label>
    <?= html_text('description', 'maxlength="500"') ?>
    <?= err('description') ?>

    <label for="price">Price</label>
    <?= html_number('price', 0.01, 99.99, 0.01) ?>
    <?= err('price') ?>

    <label for="stock_quantity">Stock Quantity</label>
    <?= html_number('stock_quantity', 1, 100, 1) ?>
    <?= err('stock_quantity') ?>

    <label for="product_photo">Photo</label>
    <label class="upload" tabindex="0">
        <?= html_file('product_photo', 'image/*', 'hidden') ?>
        <img src="/images/<?= $product_photo ?? 'photo.jpg' ?>">
    </label>
    <?= err('product_photo') ?>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../../_foot.php';