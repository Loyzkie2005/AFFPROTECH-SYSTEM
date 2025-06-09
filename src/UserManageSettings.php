<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}
$fullname = $_SESSION["fullname"] ?? 'Guest';
$username = $_SESSION["username"] ?? '';
$profileImage = '../img/profile.png';

require_once 'DBconnection.php';

// Fetch current user info
$userId = $_SESSION['user_id'] ?? null;
$user = null;
if ($userId) {
    $stmt = $conn->prepare('SELECT email, profile_image, password FROM afpro_users WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!empty($user['profile_image'])) {
        $profileImage = $user['profile_image'];
    }
    $stmt->close();
}

// Add a function to mask the email
function mask_email($email) {
    $atPos = strpos($email, '@');
    if ($atPos === false) return $email;
    $name = substr($email, 0, $atPos);
    $domain = substr($email, $atPos);
    if (strlen($name) <= 3) {
        return str_repeat('*', strlen($name)) . $domain;
    }
    return substr($name, 0, 3) . str_repeat('*', strlen($name) - 3) . $domain;
}

// Password change logic
$password_alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (!$current_password || !$new_password || !$confirm_password) {
        $password_alert = '<div class="alert alert-danger">All password fields are required.</div>';
    } elseif ($new_password !== $confirm_password) {
        $password_alert = '<div class="alert alert-danger">New passwords do not match.</div>';
    } elseif (!password_verify($current_password, $user['password'])) {
        $password_alert = '<div class="alert alert-danger">Current password is incorrect.</div>';
    } else {
        $newPasswordHash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE afpro_users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $newPasswordHash, $userId);
        if ($stmt->execute()) {
            $password_alert = '<div class="alert alert-success">Password changed successfully!</div>';
        } else {
            $password_alert = '<div class="alert alert-danger">Failed to change password.</div>';
        }
        $stmt->close();
    }
}
?>
<section class="p-4">
    <h2 class="fs-4 fw-semibold mb-3"><i class="fas fa-cog me-2"></i>Manage Settings</h2>
    <div id="settings-alert" class="position-relative"><?php echo $password_alert; ?></div>
    <div class="row justify-content-center">
        <div class="col-md-5 d-flex align-items-stretch">
            <div class="card shadow-sm w-100" style="min-height: 500px;">
                <div class="card-header bg-primary text-white" style="background: #000080 !important;">
                    <i class="fas fa-user me-2"></i>Edit Profile
                </div>
                <div class="card-body text-center">
                    <form id="user-settings-form" action="" method="POST" enctype="multipart/form-data">
                        <div style="position: relative; display: inline-block;">
                            <img src="<?php echo htmlspecialchars($profileImage); ?>" alt="Profile Image" class="rounded-circle mb-3" style="width: 160px; height: 160px; object-fit: cover; border: 2px solid #000080; z-index: 1;">
                            <button type="button" id="camera-btn" style="position: absolute; right: 12px; bottom: 12px; background: #fff; border: none; border-radius: 50%; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.15); z-index: 10; pointer-events: auto;">
                                <i class="fas fa-camera" style="font-size: 1.7rem; color: #000080;"></i>
                            </button>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="fullname" class="form-label">Full Name</label>
                            <input type="text" class="form-control form-control-lg" id="fullname" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars(mask_email($user['email'] ?? '')) ?>" required readonly>
                        </div>
                        <div class="mb-3 text-start">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password">
                        </div>
                        <div class="mb-3 text-start">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
#settings-alert .alert {
    position: absolute;
    top: -70px;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 400px;
    z-index: 1050;
    opacity: 0;
    animation: fadeInOut 3s ease-in-out;
}
#camera-btn {
    z-index: 10 !important;
    pointer-events: auto !important;
}
@keyframes fadeInOut {
    0% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
    10% { opacity: 1; transform: translateX(-50%) translateY(0); }
    90% { opacity: 1; transform: translateX(-50%) translateY(0); }
    100% { opacity: 0; transform: translateX(-50%) translateY(-10px); }
}
</style>
<?php $conn->close(); ?>