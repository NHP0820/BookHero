<?php
include '../_base.php';

$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'member') {
    temp('info', 'Please login first');
    redirect("logout.php");
    redirect("login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $errors = [];

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }

    $imagePath = $user['profile_image'] ?? '/../images/default.jpg';

    if (!empty($_FILES['profile_image']['tmp_name'])) {
        try {
            $imagePath = save_photo((object) $_FILES['profile_image'], __DIR__ . '/../images');

            if ($user['profile_image'] !== 'default.jpg') {
                $oldPath = __DIR__ . '/../images' . $user['profile_image'];
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

$_title = 'Member Profile';

include '../_head.php';
?>

<link rel="stylesheet" href="/css/memberProfile.css">
<div class="full-page-profile">
    <div class="profile-sidebar">
        <div class="profile-image-container">
            <img id="previewImage" src="../images/<?= htmlspecialchars($user['profile_image'] ?? '/../images/default.jpg') ?>" 
                 alt="Profile Image" class="profile-img">
        </div>

        <div class="profile-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['username'] ?? '') ?></p>
            <p><strong>Role:</strong> <?= ucfirst($user['role']) ?></p>
        </div>
    </div>

    <div class="profile-content">
        <h1>Member Profile</h1>

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
