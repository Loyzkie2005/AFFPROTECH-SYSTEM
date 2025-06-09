<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}

// Include database connection
require_once 'DBconnection.php';

// Get total events
$totalEvents = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM afpro_events");
if ($result) {
    $row = $result->fetch_assoc();
    $totalEvents = $row['count'];
}

// Get total attendance records
$totalAttendance = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM afpro_attendance");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalAttendance = $row['count'];
    }

// Get the first letter of the full name for the initial
$fullname = $_SESSION["fullname"] ?? 'Guest';
$initial = strtoupper(substr($fullname, 0, 1));
$color = '#000080';

$_SESSION["loggedin"] = true;
$_SESSION["role"] = "admin";

// Example: $profileImage = $_SESSION['profile_image'] ?? 'img/default-profile.png';
$profileImage = '../img/ProfilePic.jpg'; // Replace with dynamic path if available

// Fetch up to 5 recent events (created in the last 7 days or upcoming)
$now = date('Y-m-d H:i:s');
$adminRecentEvents = [];
$eventRes = $conn->query("SELECT title, start_date FROM afpro_events WHERE start_date >= CURDATE() OR start_date >= DATE_SUB('$now', INTERVAL 7 DAY) ORDER BY start_date ASC LIMIT 5");
if ($eventRes && $eventRes->num_rows > 0) {
    while ($event = $eventRes->fetch_assoc()) {
        $adminRecentEvents[] = $event;
    }
}
// Fetch latest 3 announcements
$adminRecentAnnouncements = [];
$annRes = $conn->query("SELECT message, created_at FROM afpro_announcement ORDER BY created_at DESC LIMIT 3");
if ($annRes && $annRes->num_rows > 0) {
    while ($ann = $annRes->fetch_assoc()) {
        $adminRecentAnnouncements[] = $ann;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTP OROQUIETA AFFROTECH DASHBOARD</title>
    <link rel="stylesheet" href="Admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="img/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/js-year-calendar@latest/dist/js-year-calendar.min.css">
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo text-center mb-2" style="padding-top: 10px;">
            <img src="img/logo.jpg" alt="AFPROTECH Logo" style="height: 60px; width: 60px; object-fit: contain;">
        </div>
        <div class="sidebar-header mb-4 px-3 text-center" style="margin-buttom: 20px;">
            <h5 class="text-white mb-0 fw-bold" style="font-size: 1.5rem;">AFPROTECH</h5>
            <hr style="border: 0; border-top: 2px solid #FFF; width: 60%; margin: 12px auto 0 auto; opacity: 1;">
        </div>
        <ul class="nav flex-column px-2 flex-grow-1">
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" onclick="loadPage('AdminDashboard.php'); return false;">
                    <i class="fa-solid fa-house-user me-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#eventsSubmenu">
                    <i class="fas fa-calendar-alt me-3"></i>
                    <span class="sidebar-text">Events</span>
                    <i class="fas fa-chevron-down chevron-animate" style="margin-left:auto;"></i>
                </a>
                <div class="collapse" id="eventsSubmenu">
                    <ul class="nav flex-column ms-4 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AdminEventsForm.php', 'events-submenu')">
                                <span class="sidebar-text">Create Event</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AdminViewEvent.php', 'events-submenu')">
                                <span class="sidebar-text">View Events</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#attendanceSubmenu">
                    <i class="fas fa-clipboard-check me-3"></i>
                    <span class="sidebar-text">Attendance</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="attendanceSubmenu">
                    <ul class="nav flex-column ms-4 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AttendanceRecords.php', 'attendance-submenu')">
                                <span class="sidebar-text">Attendance Log</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#announcementsSubmenu">
                    <i class="fas fa-bullhorn me-3"></i>
                    <span class="sidebar-text">Announcements</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="announcementsSubmenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AdminAnnouncements.php', 'announcements-submenu')">
                                <span class="sidebar-text">Create Announcement</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AdminViewAnnouncements.php', 'announcements-submenu')">
                                <span class="sidebar-text">View Announcements</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#promotionsSubmenu" aria-expanded="false">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">Promotions</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="promotionsSubmenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AdminPromotion.php', 'promotions-submenu')">
                                <span class="sidebar-text">Create Promotion</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('AdminViewPromotions.php', 'promotions-submenu')">
                                <span class="sidebar-text">View Promotions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" onclick="loadPage('AdminViewFeedback.php', 'feedback-submenu')">
                    <i class="fas fa-comments me-3"></i>
                    <span class="sidebar-text">View Feedback</span>
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" onclick="loadPage('GenerateReport.php', 'reports-submenu')">
                    <i class="fas fa-file-alt me-3"></i>
                    <span class="sidebar-text">Generate Reports</span>
                </a>
            </li>
        </ul>
        <!-- Sidebar Toggle Button at the bottom -->
        <div class="sidebar-toggle-btn text-center mt-auto mb-3" style="width: 100%;">
            <button id="sidebarToggleBtn" class="btn btn-light" style="border-radius: 50%; width: 44px; height: 44px; box-shadow: 0 2px 8px rgba(0,0,0,0.07);">
                <i class="fas fa-angle-left"></i>
            </button>
        </div>
    </nav>

    <!-- Header -->
    <header class="py-2 px-5 d-flex justify-content-between align-items-center shadow-sm"
            style="background: #fff !important; color: #001f5b; width: calc(100% - 250px); position: fixed; top: 0; left: 0; z-index: 900; box-shadow: 0 2px 8px rgba(0,0,0,0.07);">
        <div class="d-flex align-items-center">
            <img src="img/affprotechicon.png" alt="Afro Tech Logo" class="me-4" style="height: 60px;">
            <h4 class="mb-0 fw-semibold ORIGI-TITLE" style="color: navy;">ASSOCIATION OF FOOD PROCESSING AND TECHNOLOGY STUDENTS</h4>
        </div>
        <div class="d-flex align-items-center gap-3 justify-content-end">
           <!-- Admin Notification Bell -->
            <div class="dropdown me-5">
                <button class="btn p-0 position-relative" id="adminNotificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: none; border: none; outline: none;">
                    <i class="fas fa-bell" style="font-size: 1.7rem; color: #000080;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="adminNotificationCount" style="font-size: 0.8rem;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow notification-dropdown-menu" aria-labelledby="adminNotificationDropdown" data-bs-auto-close="outside">
                    <li class="dropdown-header fw-bold text-primary">Notifications</li>
                    <?php if (empty($adminRecentEvents) && empty($adminRecentAnnouncements)): ?>
                        <li class="notification-empty">No new or upcoming events or announcements.</li>
                    <?php else: ?>
                        <?php foreach ($adminRecentEvents as $event): ?>
                            <li class="notification-item notification-event" style="cursor:pointer;" data-link="AdminViewEvent.php">
                                <div class="notification-avatar">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div class="notification-desc">Upcoming Event</div>
                                    <div class="notification-time"><?php echo date('F j, Y', strtotime($event['start_date'])); ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <?php foreach ($adminRecentAnnouncements as $ann): ?>
                            <li class="notification-item notification-announcement" style="cursor:pointer;" data-link="AdminViewAnnouncements.php">
                                <div class="notification-avatar">
                                    <i class="fas fa-bullhorn"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title">Announcement</div>
                                    <div class="notification-desc"><?php echo htmlspecialchars(mb_strimwidth($ann['message'], 0, 60, '...')); ?></div>
                                    <div class="notification-time"><?php echo date('F j, Y g:i A', strtotime($ann['created_at'])); ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
<!-- End Admin Notification Bell -->
            <!-- End Admin Notification Bell -->
            <div class="dropdown">
                <img src="<?php echo htmlspecialchars($profileImage); ?>"
                     id="profileDropdown"
                     class="profile-img-dropdown"
                     data-bs-toggle="dropdown"
                     aria-expanded="false">
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li>
                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Section -->
    <main class="main-content">
        <div id="main-content">
            <!-- Initial content -->
            <section class="p-4 d-flex justify-content-between align-items-start" style="gap: 32px;">
            </section>
            <!-- Dashboard Cards Row (already present) -->
            <div class="row g-4 justify-content-center mx-0">
                <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                    <div class="dashboard-card d-flex flex-column align-items-center justify-content-center h-100">
                            <div class="dashboard-icon mb-2">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="fw-bold">Total Events</div>
                            <div class="fs-3 fw-bold" style="color: #ffd600;"><?= $totalEvents ?></div>
                        </div>
                </div>
                <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                    <div class="dashboard-card d-flex flex-column align-items-center justify-content-center h-100">
                            <div class="dashboard-icon mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="fw-bold">Total Attendance</div>
                            <div class="fs-3 fw-bold" style="color: #ffd600;"><?= $totalAttendance ?></div>
                        </div>
                </div>
                <div class="col-12 col-md-4 d-flex flex-column align-items-center">
                    <div class="dashboard-card d-flex flex-column align-items-center justify-content-center h-100" style="background: #00205b; color: #fff;">
                            <div class="dashboard-icon mb-2">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <div class="fw-bold">Promotions</div>
                            <div class="fs-3 fw-bold" style="color: #ffd600;"><?= $totalPromotions ?></div>
                        </div>
                    </div>
                </div>
            <!-- Latest Promotions Horizontal Scroll -->
            <div class="mt-4">
                <h5 class="fw-bold mb-3">Latest Promotions</h5>
                <div class="d-flex flex-row gap-4 overflow-auto" style="padding-bottom: 8px;">
                    <?php
                    if (!isset($dashboardPromotions)) {
                        $dashboardPromotions = [];
                        $promoRes = $conn->query("SELECT * FROM afpro_promotion ORDER BY start_date DESC LIMIT 5");
                        if ($promoRes && $promoRes->num_rows > 0) {
                            while ($promo = $promoRes->fetch_assoc()) {
                                $dashboardPromotions[] = $promo;
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($dashboardPromotions)): ?>
                        <?php foreach (array_slice($dashboardPromotions, 0, 5) as $promo): ?>
                            <div class="d-flex flex-column align-items-center justify-content-center" style="min-width: 180px;">
                                <img src="<?php echo htmlspecialchars($promo['image_url']); ?>" class="dashboard-promo-img mb-2" alt="Promotion Image" style="width: 140px; height: 140px; object-fit: cover; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.10); border: 4px solid #fff; background: #fff;">
                                <div class="dashboard-promo-title text-center" style="font-size: 1.1rem; font-weight: 600; color: #00205b; margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">
                                    <?php echo htmlspecialchars($promo['title']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No promotions found.</div>
                    <?php endif; ?>
                </div>
            </div>
    </main>

    <!-- Delete Attendance Modal -->
    <div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-labelledby="deleteAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); width: 350px; margin: auto; background-color: #fff;">
                <div class="modal-body text-center" style="padding: 30px 20px;">
                    <p class="delete-message" id="deleteAttendanceModalLabel" style="font-size: 20px; font-weight: 600; color: #000; margin: 0;">Are you sure you want to delete this attendance record?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-3" style="border-top: none; padding: 20px;">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAttendanceBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Attendance Modal -->
    <div class="modal fade" id="updateAttendanceModal" tabindex="-1" aria-labelledby="updateAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); background-color: #fff;">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateAttendanceModalLabel">Update Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="attendance-update-form">
                        <input type="hidden" id="attendanceId" name="attendance_id">
                        <input type="hidden" id="update_user_id" name="user_id">
                        <input type="hidden" id="update_event_id" name="event_id">
                        <div class="mb-3">
                            <label for="update_check_in_time" class="form-label">Check-In Time</label>
                            <input type="datetime-local" class="form-control" id="update_check_in_time" name="check_in_time">
                        </div>
                        <div class="mb-3">
                            <label for="update_check_out_time" class="form-label">Check-Out Time</label>
                            <input type="datetime-local" class="form-control" id="update_check_out_time" name="check_out_time">
                        </div>
                        <div class="mb-3">
                            <label for="update_status" class="form-label">Status</label>
                            <select class="form-control" id="update_status" name="status" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); width: 350px; margin: auto; background-color: #fff;">
                <div class="modal-body text-center" style="padding: 30px 20px;">
                    <p class="logout-message" style="font-size: 20px; font-weight: 600; color: #000; margin: 0;">Are you sure you want to log out?</p>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-3" style="border-top: none; padding: 20px;">
                    <button type="button" class="btn btn-no" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-yes" id="modal-logout-btn">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Announcement Modal -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1" aria-labelledby="editAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editAnnouncementForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAnnouncementModalLabel">Edit Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="announcement-alert-container"></div>
                        <input type="hidden" id="editAnnouncementId" name="announcement_id">
                        <div class="mb-3">
                            <label for="editMessage" class="form-label">Announcement Message</label>
                            <textarea class="form-control" id="editMessage" name="message" rows="6" required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="editCreatedBy" class="form-label">Created By</label>
                            <input type="text" class="form-control" id="editCreatedBy" name="created_by" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Announcement Modal -->
    <div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" aria-labelledby="deleteAnnouncementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAnnouncementModalLabel">Delete Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this announcement?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteAnnouncementBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="Admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-year-calendar@latest/dist/js-year-calendar.min.js"></script>
    <script>
        const dashboardEvents = <?= json_encode($conn->query('SELECT title, start_date, description, location FROM afpro_events')->fetch_all(MYSQLI_ASSOC)); ?>;
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof loadPage === 'function') {
            loadPage('AdminDashboard.php');
        }
    });
    </script>
    <script>
    // Admin Notification badge count and seen logic
    (function() {
        var count = <?php echo count($adminRecentEvents) + count($adminRecentAnnouncements); ?>;
        var badge = document.getElementById('adminNotificationCount');
        var storedCount = parseInt(localStorage.getItem('adminNotificationCount') || '0', 10);
        var seen = localStorage.getItem('adminNotificationsSeen') === 'true';

        // If the notification count increased, reset seen flag
        if (count > storedCount) {
            localStorage.setItem('adminNotificationsSeen', 'false');
            seen = false;
        }
        // Always update the stored count
        localStorage.setItem('adminNotificationCount', count);

        if (badge) {
            if (count > 0 && !seen) {
                badge.textContent = count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    })();

    // Hide notification badge when dropdown is opened (mark as seen)
    document.addEventListener('DOMContentLoaded', function() {
        var notificationDropdown = document.getElementById('adminNotificationDropdown');
        var badge = document.getElementById('adminNotificationCount');
        if (notificationDropdown && badge) {
            notificationDropdown.addEventListener('click', function() {
                badge.style.display = 'none';
                localStorage.setItem('adminNotificationsSeen', 'true');
            });
        }
        // Make notifications clickable
        document.querySelectorAll('.notification-item').forEach(function(item) {
            item.addEventListener('click', function() {
                var link = this.getAttribute('data-link');
                if (link && typeof loadPage === 'function') {
                    loadPage(link);
                }
            });
        });
        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll('.notification-dropdown-menu').forEach(function(menu) {
            menu.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>