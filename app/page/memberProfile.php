<?php
include '../_base.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'member') {
    temp('info', 'Please login first');
    redirect("logout.php");
    redirect("login.php");
    exit;
}

$stmt = $_db->prepare('
    SELECT address_id, street, city, state, zip_code, country, defaults
    FROM address
    WHERE user_id = ?
');
$stmt->execute([$user['id']]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $errors = [];

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }

    $imagePath = $user['profile_image'] ?? 'default.png';

    if (!empty($_FILES['profile_image']['tmp_name'])) {
        try {
            $imagePath = save_photo((object) $_FILES['profile_image'], __DIR__ . '/../images');

            if ($user['profile_image'] !== 'default.jpg') {
                $oldPath = __DIR__ . '../images' . $user['profile_image'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
        } catch (Exception $e) {
            $errors['profile_image'] = 'Failed to upload profile image.';
        }
    }

    if (empty($errors)) {
        $stmt = $_db->prepare("UPDATE user SET username = ?, profile_image = ? WHERE user_id = ?");
        $stmt->execute([$username, $imagePath, $user['id']]);

        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['profile_image'] = $imagePath;

        temp('info', 'Profile updated successfully.');
        redirect('/page/memberProfile.php');
        exit;
    } else {
        foreach ($errors as $key => $value) {
            err($key, $value);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_address_id'])) {
    $addressId = $_POST['delete_address_id'];

    $stmt = $_db->prepare("DELETE FROM address WHERE address_id = ?");
    $stmt->execute([$addressId]);

    temp('info', 'Address deleted successfully.');
    redirect('memberProfile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_default_address_id'])) {
    $addressId = $_POST['set_default_address_id'];

    $stmt = $_db->prepare("UPDATE address SET defaults = 0 WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    $stmt = $_db->prepare("UPDATE address SET defaults = 1 WHERE address_id = ? AND user_id = ?");
    $stmt->execute([$addressId, $user['id']]);

    temp('info', 'Address set as default successfully.');
    redirect('memberProfile.php');
    exit;
}



$_title = 'Member Profile';

include '../_head.php';
?>
<style>
    .add_new_address{
        text-decoration: none;
        color: black;
    }
    .add_new_address:hover{
        color: blue;
    }
</style>
<link rel="stylesheet" href="/css/memberProfile.css">
<div class="full-page-profile">
    <div class="profile-sidebar">
        <div class="profile-image-container">
            <img id="previewImage" src="../images/<?= htmlspecialchars($user['profile_image'] ?? 'default.png') ?>" 
                 alt="Profile Image" class="profile-img">
        </div>

        <div class="profile-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['username'] ?? '') ?></p>
            <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
        </div>
    </div>

    <div class="profile-content">
        <h1>Member Profile</h1>
        <div class="address-info">
            <h2>Address Information</h2>
            <a href="cart/addresses.php" class="add_new_address">Add New Address</a>
            <?php if ($addresses): ?>
                <table class="address-table">
                    <thead>
                        <tr>
                            <th>Street</th>
                            <th>City</th>
                            <th>State</th>
                            <th>Zip Code</th>
                            <th>Country</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($addresses as $address): ?>
                            <tr>
                                <td><?= htmlspecialchars($address['street']) ?></td>
                                <td><?= htmlspecialchars($address['city']) ?></td>
                                <td><?= htmlspecialchars($address['state']) ?></td>
                                <td><?= htmlspecialchars($address['zip_code']) ?></td>
                                <td><?= htmlspecialchars($address['country']) ?></td>
                                <td><?= $address['defaults'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <a href="cart/addresses.php?edit=<?= $address['address_id'] ?>">Edit</a> |
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="delete_address_id" value="<?= $address['address_id'] ?>">
                                        <input type="submit" value="Delete" style="color: red;" onclick="return confirm('Are you sure you want to delete this address?')">
                                    </form>
                                    <?php if (!$address['defaults']): ?>
                                        | <form method="post" style="display: inline;">
                                            <input type="hidden" name="set_default_address_id" value="<?= $address['address_id'] ?>">
                                            <input type="submit" value="Set as Default" style="color: green;">
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No address found for this user.</p>
            <?php endif; ?>
        </div>

        <form method="post" enctype="multipart/form-data" class="profile-form">
            <div class="form-section">
                <h2>Account Information</h2>

                <div class="form-row">
                    <label>Member ID</label>
                    <p><?= htmlspecialchars($user['id'] ?? '') ?></p>
                </div>

                <div class="form-row">
                    <label>Email</label>
                    <p><?= htmlspecialchars($user['email'] ?? '') ?></p>
                </div>

                <div class="form-row">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                    <?= err('username') ?>
                </div>

                <div class="form-row image-row">
                    <label for="profile_image">Profile Image</label>
                    <div class="image-preview-wrapper">
                        <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="previewImage(event)">
                    </div>
                    <?= err('profile_image') ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="update-btn">
                    <i class="fa fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const [file] = event.target.files;
    if (file) {
        const preview = document.getElementById('previewImage');
        preview.src = URL.createObjectURL(file);
    }
}
</script>

<style>
/* Keep the same styles as in the staff profile */
<?= file_get_contents(__DIR__ . '/staff_profile_styles.css') ?>
</style>

<?php include '../_foot.php'; ?>
