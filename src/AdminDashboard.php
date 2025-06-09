<?php
require_once 'DBconnection.php';

// Get total events
$totalEvents = $conn->query("SELECT COUNT(*) as count FROM afpro_events")->fetch_assoc()['count'];

// Get total attendance
$totalAttendance = $conn->query("SELECT COUNT(*) as count FROM afpro_attendance")->fetch_assoc()['count'];

// Get total promotions
$totalPromotions = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM afpro_promotion");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPromotions = $row['count'];
}

// Fetch the latest announcement
$latestAnnouncement = null;
$latestAnnouncementProfileImg = 'img/profile.png';
$result = $conn->query("SELECT * FROM afpro_announcement ORDER BY created_at DESC LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $latestAnnouncement = $row;
    // Fetch profile image for the creator
    $createdBy = $row['created_by'];
    $userResult = $conn->query("SELECT profile_image FROM afpro_users WHERE CONCAT(first_name, ' ', last_name) = '" . $conn->real_escape_string($createdBy) . "'");
    if ($userResult && $userRow = $userResult->fetch_assoc()) {
        if (!empty($userRow['profile_image'])) {
            $latestAnnouncementProfileImg = $userRow['profile_image'];
        }
    }
}
?>
<section class="p-4">
    <!-- Dashboard Cards Centered -->
    <div class="d-flex flex-column justify-content-center align-items-center gap-4" style="width: 100%;">
        <!-- Dashboard Cards -->
        <div class="row g-4 justify-content-center mx-0" style="max-width: 900px;">
            <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                <div class="dashboard-card d-flex flex-column align-items-center justify-content-center w-100">
                    <div class="dashboard-icon mb-2">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="fw-bold">Total Events</div>
                    <div class="fs-3 fw-bold" style="color: #ffd600;"><?= $totalEvents ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                <div class="dashboard-card d-flex flex-column align-items-center justify-content-center w-100">
                    <div class="dashboard-icon mb-2">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="fw-bold">Total Attendance</div>
                    <div class="fs-3 fw-bold" style="color: #ffd600;"><?= $totalAttendance ?></div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                <div class="dashboard-card d-flex flex-column align-items-center justify-content-center w-100" style="background: #00205b; color: #fff;">
                    <div class="dashboard-icon mb-2">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="fw-bold">Promotions</div>
                    <div class="fs-3 fw-bold" style="color: #ffd600;"><?= $totalPromotions ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Announcements Preview at the bottom, landscape style -->
<section class="p-4">
    <div class="container" style="max-width: 1100px; margin: 0 auto;">
        <div class="card shadow mb-4" style="width: 100%; margin: 0 auto; border-radius: 12px;">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center px-3 py-2" style="background: #000080 !important; border-radius: 12px 12px 0 0; width: 100%; display: flex; margin-bottom: 0; border-bottom: none;">
                <div class="fw-bold d-flex align-items-center" style="font-size: 1.15rem;">
                    <i class="fas fa-bullhorn me-2"></i>
                    <span class="card-title mb-0">Announcement</span>
                </div>
                <?php if (!empty($latestAnnouncement)): ?>
                    <small class="ms-auto" style="font-size: 1rem; font-weight: 400;"><?php echo date('F j, Y g:i A', strtotime($latestAnnouncement['created_at'])); ?></small>
                <?php endif; ?>
            </div>
            <div class="card-body" style="background: none; padding: 24px 32px; width: 100%; max-width: 100%; border-radius: 0 0 12px 12px;">
                <?php if (empty($latestAnnouncement)): ?>
                    <div class="text-center text-muted">No announcements at the moment.</div>
                <?php else: ?>
                    <p class="card-text mb-2" style="font-size: 1.1rem; word-break: break-word; white-space: pre-line; max-height: 220px; overflow-y: auto;"><?php echo nl2br(htmlspecialchars($latestAnnouncement['message'])); ?></p>
                    <div class="mt-2">
                        <img src="<?php echo htmlspecialchars($latestAnnouncementProfileImg); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 6px; border: 1.5px solid #000080; vertical-align: middle;">
                        <span class="text-muted">Posted by: <?php echo htmlspecialchars($latestAnnouncement['created_by']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php $conn->close(); ?>