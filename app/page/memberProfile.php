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
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        // Handle profile update (username, profile image)
        $username = trim($_POST['username'] ?? '');

        if (empty($username)) {
            $_err['username'] = 'Username is required.';
        }

        $imagePath = $user['profile_image'] ?? 'default.png';

        if (!empty($_FILES['profile_image']['tmp_name'])) {
            try {
                $imagePath = save_photo((object) $_FILES['profile_image'], __DIR__ . '/../images');

                if ($user['profile_image'] !== 'default.jpg') {
                    $oldPath = __DIR__ . '/../images/' . $user['profile_image'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
            } catch (Exception $e) {
                $_err['profile_image'] = 'Failed to upload profile image.';
            }
        }

        if (empty($_err)) {
            $stmt = $_db->prepare("UPDATE user SET username = ?, profile_image = ? WHERE user_id = ?");
            $stmt->execute([$username, $imagePath, $user['id']]);

            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['profile_image'] = $imagePath;

            temp('info', 'Profile updated successfully.');
            redirect('/page/memberProfile.php');
            exit;
        } else {
            foreach ($_err as $key => $value) {
                err($key, $value);
            }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $stmt = $_db->prepare("SELECT password FROM user WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $userPassword = $stmt->fetchColumn();

    if (!password_verify($currentPassword, $userPassword)) {
        $_err['current_password'] = 'Current password is incorrect.';
    } elseif ($newPassword !== $confirmPassword) {
        $_err['confirm_password'] = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $_err['new_password'] = 'New password must be at least 6 characters.';
    }

    if (empty($_err)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $_db->prepare("UPDATE user SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $user['id']]);

        temp('info', 'Password changed successfully.');
        redirect('/page/memberProfile.php');
        exit;
    } else {
        foreach ($_err as $key => $value) {
            err($key, $value);
        }
    }
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
            <input type="hidden" name="action" value="update_profile">
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
                </div>
                <?= err('username') ?>

                <div class="form-row image-row">
                    <label for="profile_image">Profile Image</label>
                    <div class="drop-zone" id="dropZone">
                    <img id="previewImage" src="../images/<?= htmlspecialchars($user['profile_image'] ?? 'default.png') ?>" alt="Profile Image" class="profile-img">
                        <input type="file" name="profile_image" id="profile_image" accept="image/*" style="display: none;">
                        <p id="dropText">Drag & drop or click to upload</p>
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

        <form method="post" class="profile-form">
            <input type="hidden" name="action" value="change_password">
            <div class="form-section">
                <h2>Change Password</h2>

                <div class="form-row">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <?= err('current_password') ?>

                <div class="form-row">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <?= err('new_password') ?>

                <div class="form-row">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <?= err('confirm_password') ?>

                <div class="form-actions">
                    <button type="submit" class="update-btn">
                        <i class="fa fa-lock"></i> Change Password
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('profile_image');
    const preview = document.getElementById('previewImage');
    const dropText = document.getElementById('dropText');

    // Click dropzone to open file dialog
    dropZone.addEventListener('click', () => fileInput.click());

    // Handle file select normally
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            updatePreview(e.target.files[0]);
        }
    });

    // Handle drag over
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    // Handle drag leave
    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    // Handle drop
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');

        if (e.dataTransfer.files.length) {
            const file = e.dataTransfer.files[0];
            fileInput.files = e.dataTransfer.files;
            updatePreview(file);
        }
    });

    function updatePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            dropText.textContent = 'Image selected!';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<style>
/* Keep the same styles as in the staff profile */
<?= file_get_contents(__DIR__ . '/staff_profile_styles.css') ?>
</style>

<?php include '../_foot.php'; ?>
