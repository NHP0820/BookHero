<?php
include '../../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role != 'admin') {
    temp('info', 'Please login first');
    redirect("../staffLogin.php");
    exit;
}

// ----------------------------------------------------------------------------

if (is_post()) {
    $id = req('id');

    // Begin transaction
    $_db->beginTransaction();

    try {
        // Delete from category_product first (foreign key constraint)
        $stm = $_db->prepare('DELETE FROM category_product WHERE product_id = ?');
        $stm->execute([$id]);

        // Get product photo filename before deleting
        $stm = $_db->prepare('SELECT product_photo FROM product_photo WHERE product_id = ?');
        $stm->execute([$id]);
        $photo = $stm->fetchColumn();

        // Delete from product_photo
        $stm = $_db->prepare('DELETE FROM product_photo WHERE product_id = ?');
        $stm->execute([$id]);

        // Delete from product
        $stm = $_db->prepare('DELETE FROM product WHERE product_id = ?');
        $stm->execute([$id]);

        // Commit transaction
        $_db->commit();

        // Delete the photo file if it exists
        if ($photo && file_exists("../photos/$photo")) {
            unlink("../photos/$photo");
        }

        temp('info', 'Product deleted successfully');
        redirect('index.php');

    } catch (Exception $ex) {
        $_db->rollBack();
        temp('danger', 'Failed to delete product');
        redirect('index.php');
    }
}

// If not POST request, redirect to index
redirect('index.php');