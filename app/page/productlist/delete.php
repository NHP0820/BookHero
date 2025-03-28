<?php
include '../../_base.php';

// ----------------------------------------------------------------------------

if (is_post()) {
    $id = req('id');

    // Start transaction
    $_db->beginTransaction();

    try {
        // First, get and delete the photo file and record
        $stm = $_db->prepare('SELECT product_photo FROM product_photo WHERE product_id = ?');
        $stm->execute([$id]);
        $photos = $stm->fetchAll(PDO::FETCH_COLUMN);

        // Delete photo files
        foreach ($photos as $photo) {
            if ($photo && file_exists("../../images/$photo")) {
                unlink("../photos/$photo");
            }
        }

        // Delete photo records
        $stm = $_db->prepare('DELETE FROM product_photo WHERE product_id = ?');
        $stm->execute([$id]);

        // Then delete the product
        $stm = $_db->prepare('DELETE FROM product WHERE product_id = ?');
        $stm->execute([$id]);

        // Commit transaction
        $_db->commit();

        temp('info', 'Record deleted');
    } catch (Exception $ex) {
        // Rollback on error
        $_db->rollBack();
        temp('error', 'Failed to delete record');
    }
}

redirect('index.php');

// ----------------------------------------------------------------------------