<?php
ini_set('session.cookie_path', '/');
session_start();
// AJAX-friendly session/role check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo '<div class="alert alert-danger m-3">
                <h4 class="alert-heading">Session Expired</h4>
                <p>Your session has expired or you do not have permission to access this page.</p>
                <hr>
                <p class="mb-0">Please <a href="Login.php">log in</a> again.</p>
              </div>';
        exit;
    } else {
        header("Location: Login.php");
        exit;
    }
}
require_once 'DBconnection.php'; // Use the correct DB connection file

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
        .btn-warning, .btn-danger {
            font-weight: 600 !important;
            border-radius: 10px !important;
            padding: 0.375rem 1.25rem !important;
            font-size: 1rem !important;
            min-width: 100px;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-warning {
            color: #000 !important;
            background: #ffc107 !important;
            border: none !important;
        }
        .btn-warning:hover, .btn-warning:focus {
            background: #ffcd39 !important;
            color: #000 !important;
        }
        .btn-danger {
            background: #dc3545 !important;
            color: #fff !important;
            border: none !important;
        }
        .btn-danger:hover, .btn-danger:focus {
            background: #b52a37 !important;
            color: #fff !important;
        }
        .btn-sm {
            padding: 0.375rem 1.25rem !important;
            font-size: 1rem !important;
        }
        .btn i {
            font-size: 1.1rem;
            margin-right: 0.5em;
        }
        .card {
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
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
                                <div class="mt-3">
                                    <img src="<?= htmlspecialchars($row['profile_image']) ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 6px; border: 1.5px solid #000080; vertical-align: middle;">
                                    <span>Posted by: <?= htmlspecialchars($row['created_by']) ?></span>
                                </div>
                            </div>
                            <div class="card-footer d-flex gap-2 justify-content-end bg-white border-0">
                                <button type="button" class="btn btn-sm btn-warning btn-edit-announcement"
                                    data-id="<?= $row['announcement_id'] ?>"
                                    data-message="<?= htmlspecialchars($row['message'], ENT_QUOTES) ?>"
                                    data-created_by="<?= htmlspecialchars($row['created_by'], ENT_QUOTES) ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-announcement"
                                    data-id="<?= $row['announcement_id'] ?>"
                                    data-message="<?= htmlspecialchars($row['message'], ENT_QUOTES) ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Edit Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editAnnouncementForm">
        <div class="modal-header">
          <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editAnnouncementId" name="announcement_id">
            <div class="mb-3">
                <label for="editMessage" class="form-label">Message</label>
                <textarea class="form-control" id="editMessage" name="message" required minlength="10"></textarea>
            </div>
            <div class="mt-3">
                <label for="editCreatedBy" class="form-label">Created By</label>
                <input type="text" class="form-control" id="editCreatedBy" name="created_by" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAnnouncementModalLabel">Delete Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this announcement?
        <input type="hidden" id="deleteAnnouncementId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteAnnouncementBtn">Delete</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="Admin.js"></script>

</body>
</html>
