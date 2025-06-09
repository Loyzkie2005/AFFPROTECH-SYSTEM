<?php
session_start();
require_once 'DBconnection.php';

// Only allow logged-in users
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}

// Fetch announcements
$announcements = [];
$result = $conn->query("SELECT * FROM afpro_announcement ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Fetch profile image for the creator
        $profileImg = 'img/profile.png';
        $createdBy = $row['created_by'];
        $userResult = $conn->query("SELECT profile_image FROM afpro_users WHERE CONCAT(first_name, ' ', last_name) = '" . $conn->real_escape_string($createdBy) . "'");
        if ($userResult && $userRow = $userResult->fetch_assoc()) {
            if (!empty($userRow['profile_image'])) {
                $profileImg = $userRow['profile_image'];
            }
        }
        $row['profile_image'] = $profileImg;
        $announcements[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .card-header.bg-primary {
            background: #000080 !important;
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .posted-by-left {
            justify-content: flex-start !important;
            text-align: left !important;
            width: 100%;
            margin-buttom
        }
    </style>
</head>
<body>
<section class="p-4">
    <h2 class="fs-4 fw-semibold mb-3">All Announcements</h2>
    <div class="bg-white p-4 shadow-sm rounded">
        <?php if (empty($announcements)): ?>
            <div class="text-center p-4">
                <i class="fas fa-bullhorn fa-3x mb-3 text-muted"></i>
                <h3>No Announcements</h3>
                <p>There are no announcements at the moment.</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 g-4">
                <?php foreach ($announcements as $row): ?>
                    <div class="col">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-bullhorn me-2"></i>
                                    <span class="card-title mb-0">Announcement</span>
                                </div>
                                <small><?= date('M j, Y g:i A', strtotime($row['created_at'])) ?></small>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                                <div class="mb-3 d-flex align-items-center posted-by-left">
                                    <img src="<?= htmlspecialchars($row['profile_image']) ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 6px; border: 1.5px solid #000080; vertical-align: middle;">
                                    <span>Posted by: <?= htmlspecialchars($row['created_by']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 