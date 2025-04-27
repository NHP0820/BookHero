<?php
include '../_base.php';

$_title = 'Staff Profile';

$user = $_SESSION['user'] ?? [];
if (empty($user)) {
    temp('danger', 'Please login first');
    redirect('/page/login.php');
}

if ($user['role'] !== 'admin') {
    temp('danger', 'Access denied');
    redirect('/');
}

if (is_post()) {
    $username = req('username');
    $file = get_file('profile_image');

    if (strlen($username) < 3) {
        $_err['username'] = 'Username must be at least 3 characters';
    }

    if ($file) {
        if (!str_starts_with($file->type, 'image/')) {
            $_err['profile_image'] = 'Must be an image file';
        } else if ($file->size > 2 * 1024 * 1024) {
            $_err['profile_image'] = 'Maximum 2MB allowed';
        }
    }

    if (!$_err) {
        $data = ['username' => $username];
        $currentImage = $user['profile_image'] ?? null;

        if ($file) {
            $uploadDir = __DIR__ . '/../uploads/profiles/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $imageName = uniqid() . '.jpg';
            $targetPath = $uploadDir . $imageName;
            
            if (move_uploaded_file($file->tmp_name, $targetPath)) {
                $data['profile_image'] = $imageName;
                
                if ($currentImage && file_exists($uploadDir . $currentImage)) {
                    @unlink($uploadDir . $currentImage);
                }
            }
        }

        $stmt = $_db->prepare('UPDATE user SET username = ?, profile_image = ? WHERE user_id = ?');
        $stmt->execute([
            $data['username'], 
            $data['profile_image'] ?? $currentImage, 
            $user['user_id']
        ]);
        
        $_SESSION['user']['username'] = $data['username'];
        if (isset($data['profile_image'])) {
            $_SESSION['user']['profile_image'] = $data['profile_image'];
        }
        
        temp('info', 'Profile updated successfully');
        redirect();
    }
}

// Fetch fresh data
$stmt = $_db->prepare('SELECT email FROM user WHERE user_id = ?');
$stmt->execute([$user['id']]);
$dbUser = $stmt->fetch();
$user['email'] = $dbUser->email ?? $user['email'] ?? '';

include '../_staffHead.php';
?>

<form method="post" enctype="multipart/form-data" class="profile-form">
    <div class="full-page-profile">
        <!-- Left side: Profile photo -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-image-container">
                    <img src="/uploads/profiles/<?= htmlspecialchars($user['profile_image'] ?? 'default.jpg') ?>" 
                         alt="Profile Image" class="profile-img" id="profile-preview">
                    <label class="image-upload-btn">
                        <i class="fa fa-camera"></i>
                        <input type="file" name="profile_image" accept="image/*" id="image-upload">
                    </label>
                    <?= err('profile_image') ?>
                </div>
                
                <div class="profile-info">
                    <h3><?= htmlspecialchars($user['username'] ?? '') ?></h3>
                    <p class="role-badge">Administrator</p>
                </div>
            </div>
        </div>

        <!-- Right side: Account details -->
        <div class="profile-main">
            <div class="profile-header">
                <h1><i class="fa fa-user-cog"></i> Staff Profile</h1>
            </div>
            
            <div class="form-section">
                <h2><i class="fa fa-id-card"></i> Account Details</h2>
                
                <div class="form-row">
                    <div class="form-col">
                        <label>Staff ID</label>
                        <div class="form-value"><?= htmlspecialchars($user['user_id'] ?? '') ?></div>
                    </div>
                    
                    <div class="form-col">
                        <label>Role</label>
                        <div class="form-value">Administrator</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <label>Email</label>
                        <div class="form-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2><i class="fa fa-edit"></i> Edit Profile</h2>
                
                <div class="form-row">
                    <div class="form-col">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?= htmlspecialchars($user['username'] ?? '') ?>"
                               class="form-input">
                        <?= err('username') ?>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-save">
                    <i class="fa fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</form>


<head>
<script src="/js/app.js"></script>
<link rel="stylesheet" href="/css/Profile.css">
</head>

<?php include '../_foot.php'; ?>