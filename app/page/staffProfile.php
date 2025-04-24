<?php
include '../_base.php';

$user_id = $_SESSION['user']['id'] ?? null;
$user_role = $_SESSION['user']['role'] ?? null;
if (!$user_id || $user_role !== 'admin') {
    temp('info', 'Please login first');
    redirect("login.php");
    redirect("staffLogin.php");
    exit;
}


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

// [Keep all your existing form handling code...]

include '../_staffHead.php';
?>

<div class="full-page-profile">
    <div class="profile-sidebar">
        <div class="profile-image-container">
            <img src="/uploads/profiles/<?= htmlspecialchars($user['profile_image'] ?? 'default.jpg') ?>" 
                 alt="Profile Image" class="profile-img">
            <label class="image-upload-btn">
                <i class="fa fa-camera"></i>
                <input type="file" name="profile_image" accept="image/*">
            </label>
            <?= err('profile_image') ?>
        </div>
        
        <div class="profile-info">
            <h3><?= htmlspecialchars($user['username'] ?? '') ?></h3>
            <p class="role-badge"><?= ucfirst($user['role']) ?></p>
        </div>
    </div>

    <div class="profile-content">
        <h1>Staff Profile</h1>
        
        <form method="post" enctype="multipart/form-data" class="profile-form">
            <div class="form-section">
                <h2>Account Information</h2>
                
                <div class="form-row">
                    <label>Staff ID</label>
                    <p><?= htmlspecialchars($user['user_id'] ?? '') ?></p>
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
            </div>
            
            <div class="form-actions">
                <button type="submit" class="update-btn">
                    <i class="fa fa-save"></i> Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<style>

.full-page-profile {
    display: flex;
    min-height: calc(100vh - 120px); 
    background: #f8f9fa;
}

.profile-sidebar {
    width: 300px;
    background: #02aaf7;
    color: white;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.profile-content {
    flex: 1;
    padding: 2rem;
    background: white;
}

/* Profile image styles */
.profile-image-container {
    position: relative;
    margin-bottom: 1.5rem;
}

.profile-img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #007bff;
}

.image-upload-btn {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: #007bff;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.image-upload-btn input {
    display: none;
}

/* Form styles */
.profile-form {
    max-width: 800px;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.form-row {
    display: flex;
    margin-bottom: 1rem;
    align-items: center;
}

.form-row label {
    width: 150px;
    font-weight: bold;
}

.form-row input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.role-badge {
    background: #007bff;
    padding: 0.3rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.update-btn {
    background: #007bff;
    color: white;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background 0.3s;
}

.update-btn:hover {
    background: #02aaf7;
}
</style>

<?php include '../_foot.php'; ?>