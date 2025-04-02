<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $category = req('category');  // Changed from category_id
    $name = req('name');          // Changed from category_name

    // Validate: name
    if ($name == '') {
        $_err['name'] = 'Required';  // Changed from category_name
    }
    else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum 100 characters';  // Changed from category_name
    }

    // DB operation
    if (!$_err) {
        $stm = $_db->prepare('
            INSERT INTO category (name)  <!-- Changed column name -->
            VALUES (?)
        ');
        $stm->execute([$name]);

        temp('info', 'Record inserted');
        redirect('category.php');
    }
}

// ----------------------------------------------------------------------------

$_title = 'Category | Insert';
include '../_head.php';
?>

<p>
    <button data-get="category.php">Category</button>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="name">Name</label>  <!-- Changed from category_name -->
    <?= html_text('name', 'maxlength="100"') ?>  <!-- Changed from category_name -->
    <?= err('name') ?>  <!-- Changed from category_name -->

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
include '../_foot.php';