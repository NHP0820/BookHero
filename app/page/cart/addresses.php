<?php
require '../../_base.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user']['id'];
$errors = [];
$success = "";

$addressesStmt = $_db->prepare("SELECT * FROM address WHERE user_id = ? ORDER BY address_id DESC");
$addressesStmt->execute([$userId]);
$addresses = $addressesStmt->fetchAll(PDO::FETCH_OBJ);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_address_id'])) {
        $deleteId = intval($_POST['delete_address_id']);
        
        if (count($addresses) <= 1) {
            $errors[] = "You cannot delete your only address.";
        } else {
            $deleteStmt = $_db->prepare("DELETE FROM address WHERE address_id = ? AND user_id = ?");
            $deleteStmt->execute([$deleteId, $userId]);
            
            if ($deleteStmt->rowCount() > 0) {
                $success = "Address deleted successfully.";
                header("Location: addresses.php?success=deleted");
                exit();
            } else {
                $errors[] = "Failed to delete address.";
            }
        }
    } 
    else {
        $street = trim($_POST['street'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zipCode = trim($_POST['zip_code'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $addressId = isset($_POST['address_id']) ? intval($_POST['address_id']) : null;

        if (empty($street)) {
            $errors[] = "Street address is required.";
        }
        if (empty($city)) {
            $errors[] = "City is required.";
        }
        if (empty($state)) {
            $errors[] = "State is required.";
        }
        if (empty($zipCode)) {
            $errors[] = "ZIP/Postal code is required.";
        }
        if (empty($country)) {
            $errors[] = "Country is required.";
        }

        if (empty($errors)) {
            try {
                if ($addressId) {
                    $updateStmt = $_db->prepare("
                        UPDATE address 
                        SET street = ?, city = ?, state = ?, zip_code = ?, country = ? 
                        WHERE address_id = ? AND user_id = ?
                    ");
                    $updateStmt->execute([$street, $city, $state, $zipCode, $country, $addressId, $userId]);
                    $success = "Address updated successfully.";
                } else {
                    $insertStmt = $_db->prepare("
                        INSERT INTO address (user_id, street, city, state, zip_code, country)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $insertStmt->execute([$userId, $street, $city, $state, $zipCode, $country]);
                    $success = "Address added successfully.";
                }
                
                header("Location: addresses.php?success=" . ($addressId ? "updated" : "added"));
                exit();
                
            } catch (Exception $e) {
                $errors[] = "An error occurred: " . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'added':
            $success = "Address added successfully.";
            break;
        case 'updated':
            $success = "Address updated successfully.";
            break;
        case 'deleted':
            $success = "Address deleted successfully.";
            break;
    }
}

$editAddress = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editStmt = $_db->prepare("SELECT * FROM address WHERE address_id = ? AND user_id = ?");
    $editStmt->execute([$editId, $userId]);
    $editAddress = $editStmt->fetch(PDO::FETCH_OBJ);
}

include '../../_head.php';
?>

<link rel="stylesheet" href="../../css/addresses.css">
<head>
    <title>BookHero - Your Address</title>
</head>

<body>
    <div class="container">
        <h1 class="page-title">Your Address</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="section">
            <h2 class="section-title"><?= $editAddress ? 'Edit Address' : 'Add New Address' ?></h2>
            <form method="post" action="addresses.php">
                <?php if ($editAddress): ?>
                    <input type="hidden" name="address_id" value="<?= $editAddress->address_id ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="street">Street Address *</label>
                    <input type="text" id="street" name="street" class="form-control" required
                           value="<?= htmlspecialchars($editAddress->street ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City *</label>
                        <input type="text" id="city" name="city" class="form-control" required
                               value="<?= htmlspecialchars($editAddress->city ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State/Province *</label>
                        <input type="text" id="state" name="state" class="form-control" required
                               value="<?= htmlspecialchars($editAddress->state ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="zip_code">ZIP/Postal Code *</label>
                        <input type="text" id="zip_code" name="zip_code" class="form-control" required
                               value="<?= htmlspecialchars($editAddress->zip_code ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Country *</label>
                        <input type="text" id="country" name="country" class="form-control" required
                               value="<?= htmlspecialchars($editAddress->country ?? '') ?>">
                    </div>
                </div>
                
                <button type="submit" class="btn"><?= $editAddress ? 'Update Address' : 'Add Address' ?></button>
                
                <?php if ($editAddress): ?>
                    <a href="addresses.php" class="back-link">Cancel Edit</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="section">
            <h2 class="section-title">Your Addresses</h2>
            
            <?php if (empty($addresses)): ?>
                <p>You haven't added any addresses yet.</p>
            <?php else: ?>
                <?php foreach ($addresses as $address): ?>
                    <div class="address-card">
                        <p>
                            <?= htmlspecialchars($address->street) ?><br>
                            <?= htmlspecialchars($address->city) ?>, <?= htmlspecialchars($address->state) ?> <?= htmlspecialchars($address->zip_code) ?><br>
                            <?= htmlspecialchars($address->country) ?>
                        </p>
                        <div class="address-actions">
                            <a href="addresses.php?edit=<?= $address->address_id ?>">Edit</a>
                            <form method="post" action="addresses.php" style="display: inline;">
                                <input type="hidden" name="delete_address_id" value="<?= $address->address_id ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this address?');" 
                                    style="background: none; border: none; cursor: pointer; color: #ff4444; text-decoration: none;">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <a href="checkout.php" class="back-link">Back to Checkout</a>
    </div>
</body>
</html>