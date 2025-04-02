<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_get()) {
    $category = req('category');  // Changed from category_id

    $stm = $_db->prepare('SELECT * FROM category WHERE category = ?');  // Changed column name
    $stm->execute([$category]);
    $p = $stm->fetch();

    if (!$p) {
        redirect('category.php');
    }
}

if (is_post()) {
    $category = req('category');  // Changed from category_id
    $name = req('name');          // Changed from category_name

    // DB operation
    if (!$_err) {
        $stm = $_db->prepare('
            UPDATE category
            SET name = ?  <!-- Changed column name -->
            WHERE category = ?  <!-- Changed column name -->
        ');
        $stm->execute([$name, $category]);

        temp('info', 'Record updated');
        redirect('category.php');
    }
}

// ----------------------------------------------------------------------------

$_title = 'Category | Update';
include '../_head.php';
?>

<p>
    <button data-get="category.php">Back to Categories</button>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="name">Name</label>  <!-- Changed from category_name -->
    <?= html_text('name', 'maxlength="100" value="'.($p->name ?? '').'"') ?>  <!-- Changed from category_name -->
    <?= err('name') ?>  <!-- Changed from category_name -->

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';