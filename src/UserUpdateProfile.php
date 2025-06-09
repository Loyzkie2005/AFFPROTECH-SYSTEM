<?php
session_start();
require_once 'DBconnection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Optionally redirect or show nothing
    header('Location: UserManageSettings.php');
    exit;
}

$userId = $_SESSION["user_id"] ?? null;
if (!$userId) {
    echo '<div class="alert alert-danger">User not found</div>';
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$profileImage = null;

// Split full name into first and last name
$firstName = $fullname;
$lastName = '';
if (strpos($fullname, ' ') !== false) {
    $parts = explode(' ', $fullname, 2);
    $firstName = $parts[0];
    $lastName = $parts[1];
}

// Fetch current user info
$stmt = $conn->prepare("SELECT first_name, last_name, email, password, profile_image FROM afpro_users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($curFirst, $curLast, $curEmail, $curPasswordHash, $curProfileImage);
$stmt->fetch();
$stmt->close();

$changes = [];
if ($firstName !== $curFirst || $lastName !== $curLast) $changes[] = 'name';
if ($email !== $curEmail) $changes[] = 'email';
if ($profileImage) $changes[] = 'profile_image';
$updatePassword = false;
if ($new_password !== '' && $new_password === $confirm_password) {
    if (!password_verify($new_password, $curPasswordHash)) {
        $updatePassword = true;
        $changes[] = 'password';
    }
}

// Get current profile image if not uploading new one
$currentProfileImage = null;
$stmtImg = $conn->prepare("SELECT profile_image FROM afpro_users WHERE user_id = ?");
$stmtImg->bind_param("i", $userId);
$stmtImg->execute();
$stmtImg->bind_result($currentProfileImage);
$stmtImg->fetch();
$stmtImg->close();

// Handle file upload
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = basename($_FILES['profile_image']['name']);
    $fileSize = $_FILES['profile_image']['size'];
    $fileType = $_FILES['profile_image']['type'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileExt, $allowedExts)) {
        $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
        $destPath = '../img/' . $newFileName;
        $dbPath = '../img/' . $newFileName;  // Store the path with ../img/ prefix
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $profileImage = $dbPath;  // Use the path with ../img/ prefix
            if ($profileImage !== $curProfileImage) {
                $changes[] = 'profile_image';
            }
        } else {
            $response = [
                'success' => false,
                'message' => '<div class="alert alert-danger">Failed to upload image</div>',
                'profile_image' => $currentProfileImage
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    } elseif ($fileSize > 0) {
        $response = [
            'success' => false,
            'message' => '<div class="alert alert-danger">Invalid image file</div>',
            'profile_image' => $currentProfileImage
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

if (empty($changes)) {
    $response = [
        'success' => false,
        'message' => '<div class="alert alert-info">No changes detected.</div>',
        'profile_image' => $curProfileImage
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Build update query dynamically
$fields = [];
$params = [];
$types = '';
if (in_array('name', $changes)) {
    $fields[] = 'first_name = ?'; $fields[] = 'last_name = ?';
    $params[] = $firstName; $params[] = $lastName; $types .= 'ss';
}
if (in_array('email', $changes)) {
    $fields[] = 'email = ?'; $params[] = $email; $types .= 's';
}
if ($profileImage) {
    $fields[] = 'profile_image = ?'; $params[] = $profileImage; $types .= 's';
}
if ($updatePassword) {
    $fields[] = 'password = ?'; $params[] = password_hash($new_password, PASSWORD_DEFAULT); $types .= 's';
}
$params[] = $userId; $types .= 'i';

$sql = "UPDATE afpro_users SET " . implode(', ', $fields) . " WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
if ($stmt->execute()) {
    if (in_array('name', $changes)) $_SESSION["fullname"] = trim($firstName . ' ' . $lastName);
    if ($profileImage) $_SESSION["profile_image"] = $profileImage;
    $stmt->close();
    $conn->close();
    $finalProfileImage = $profileImage ? $profileImage : $curProfileImage;
    $response = [
        'success' => true,
        'message' => '<div class="alert alert-success">Profile Updated!</div>',
        'profile_image' => $finalProfileImage
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    $stmt->close();
    $conn->close();
    $response = [
        'success' => false,
        'message' => '<div class="alert alert-danger">Failed to update profile</div>',
        'profile_image' => $curProfileImage
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} 

