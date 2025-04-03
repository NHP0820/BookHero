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
    <button class="btnp" data-get="category.php">Back to Categories</button>
</p>

<form method="post" class="form" enctype="multipart/form-data" novalidate>
    <label for="name">Name</label>  <!-- Changed from category_name -->
    <?= html_text('name', 'maxlength="100" value="'.($p->name ?? '').'"') ?>  <!-- Changed from category_name -->
    <?= err('name') ?>  <!-- Changed from category_name -->

    <section>
        <button class="btnp">Submit</button>
        <button class="btnp" type="reset">Reset</button>
    </section>
</form>

<?php
include '../../_foot.php';