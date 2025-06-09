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

// Get user's attendance records
$userId = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;
$totalAttendance = $totalPresent = 0;
if ($userId) {
    $userId = mysqli_real_escape_string($conn, $userId);

    // Total attendance (all records for this user)
    $result = $conn->query("SELECT COUNT(*) as count FROM afpro_attendance WHERE user_id = '$userId'");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalAttendance = $row['count'];
    }

    // Total Present
    $result = $conn->query("SELECT COUNT(*) as count FROM afpro_attendance WHERE user_id = '$userId' AND status = 'Present'");
    if ($result) {
        $row = $result->fetch_assoc();
        $totalPresent = $row['count'];
    }
}

// Get total promotions
$totalPromotions = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM afpro_promotion");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPromotions = $row['count'];
}

// Get latest 3 promotions for dashboard
$dashboardPromotions = [];
$promoRes = $conn->query("SELECT * FROM afpro_promotion ORDER BY start_date DESC LIMIT 3");
if ($promoRes && $promoRes->num_rows > 0) {
    while ($promo = $promoRes->fetch_assoc()) {
        $dashboardPromotions[] = $promo;
    }
}

// Get all events for the calendar
$calendarEvents = [];
$result = $conn->query("SELECT * FROM afpro_events ORDER BY start_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $calendarEvents[] = $row;
    }
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

// Fetch up to 5 recent events (created in the last 7 days or upcoming)
$now = date('Y-m-d H:i:s');
$recentEvents = [];
$eventRes = $conn->query("SELECT title, start_date FROM afpro_events WHERE start_date >= CURDATE() OR start_date >= DATE_SUB('$now', INTERVAL 7 DAY) ORDER BY start_date ASC LIMIT 5");
if ($eventRes && $eventRes->num_rows > 0) {
    while ($event = $eventRes->fetch_assoc()) {
        $recentEvents[] = $event;
    }
}
// Fetch latest 3 announcements
$recentAnnouncements = [];
$annRes = $conn->query("SELECT message, created_at FROM afpro_announcement ORDER BY created_at DESC LIMIT 3");
if ($annRes && $annRes->num_rows > 0) {
    while ($ann = $annRes->fetch_assoc()) {
        $recentAnnouncements[] = $ann;
    }
}

// Get the first letter of the full name for the initial
$fullname = $_SESSION["fullname"] ?? 'Guest';
$initial = strtoupper(substr($fullname, 0, 1));
$profileImage = isset($_SESSION["profile_image"]) ? $_SESSION["profile_image"] : '../img/profile.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USTP OROQUIETA AFFROTECH DASHBOAR</title>
    <link rel="stylesheet" href="UserDashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="img/logo.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cal+Sans&family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Oswald:wght@200..700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/js-year-calendar@latest/dist/js-year-calendar.min.css">
    <style>
    /* Notification Dropdown Modern Style */
    .notification-dropdown-menu {
        border-radius: 18px !important;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10) !important;
        background: #fff !important;
        padding: 0.5rem 0.5rem 0.5rem 0.5rem;
        min-width: 350px;
        max-width: 400px;
        max-height: 350px;
        overflow-y: auto;
    }
    .notification-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 20px 10px 16px 10px;
        border-radius: 16px;
        background: #000080;
        color: #fff;
        margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        transition: background 0.15s;
        cursor: pointer;
        text-align: center;
        border: 2px solid #000080;
    }
    .notification-item:hover {
        background: #2323a6;
        border-color: #2323a6;
    }
    .notification-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        color: #000080;
        margin: 0 auto 6px auto;
    }
    .notification-content {
        width: 100%;
        min-width: 0;
        text-align: center;
    }
    .notification-title {
        font-weight: 800;
        color: #fff;
        font-size: 1.1rem;
        margin-bottom: 2px;
        letter-spacing: 0.01em;
    }
    .notification-desc {
        color: #e0e0e0;
        font-size: 1rem;
        margin-bottom: 2px;
    }
    .notification-time {
        color: #c7c7c7;
        font-size: 0.95rem;
    }
    .notification-empty {
        text-align: center;
        color: #aaa;
        padding: 24px 0 18px 0;
        font-size: 1.05rem;
    }
    .dropdown-header.fw-bold.text-primary {
        color: #0056b3 !important;
        font-weight: 800 !important;
        text-align: center;
        font-size: 1.2rem !important;
    }
    .card.promo-card {
        width: 340px;
        height: 370px;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.10);
        background: linear-gradient(135deg, #f8fafc 60%, #e3eaff 100%);
        border: none !important;
        border-radius: 60px/40px !important;
        margin: 0;
        padding-top: 30px;
        position: relative;
    }
    .card-img-top.promo-img {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50%;
        margin: 0 auto 10px auto;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        border: 4px solid #fff;
        background: #fff;
        position: absolute;
        top: -40px;
        left: 50%;
        transform: translateX(-50%);
    }
    .card-header {
        width: 100%;
        background: #000080 !important;
        color: #fff;
        text-align: center;
        border-top-left-radius: 18px;
        border-top-right-radius: 18px;
        padding: 0.5rem 0.5rem 0 0.5rem !important;
    }
    .card-title {
        font-size: 1.2rem !important;
        font-weight: bold;
        margin-bottom: 0;
    }
    .card-body {
        flex: 1 1 auto;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding: 0.5rem 1rem 0 1rem !important;
    }
    .card-footer {
        width: 100%;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem 1rem 1rem !important;
        background: none;
        border: none;
    }
    @media (max-width: 900px) {
        .card { width: 100%; min-width: 0; min-height: 0; max-width: 100%; max-height: none; }
        .card-img-top.promo-img { width: 100px; height: 100px; }
    }
    .dashboard-promo-img {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.10);
        border: 4px solid #fff;
        background: #fff;
        margin: 0 auto 10px auto;
        display: block;
    }
    .dashboard-promo-title {
        text-align: center;
        font-size: 1.1rem;
        font-weight: 600;
        color: #00205b;
        margin-bottom: 24px;
    }
    @media (max-width: 900px) {
        .dashboard-promo-img { width: 100px; height: 100px; }
    }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-logo text-center mb-2" style="padding-top: 10px; position: relative;">
            <img src="img/logo.jpg" alt="AFPROTECH Logo" style="height: 60px; width: 60px; object-fit: contain; margin-bottom: 6px;">
        </div>
        <div class="sidebar-header mb-4 px-3 text-center">
            <h5 class="text-white mb-0 fw-bold" style="font-size: 1.5rem;">AFPROTECH</h5>
            <hr style="border: 0; border-top: 2px solid #FFF; width: 60%; margin: 12px auto 0 auto; opacity: 1;">
        </div>
        <ul class="nav flex-column px-2 flex-grow-1">
            <li class="nav-item mb-3">
                <a href="UserDashboard.php" class="nav-link text-white d-flex align-items-center">
                    <i class="fa-solid fa-house-user me-3"></i>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#eventsSubmenu" onclick="return false;">
                    <i class="fas fa-calendar-alt me-3"></i>
                    <span class="sidebar-text">Events</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="eventsSubmenu">
                    <ul class="nav flex-column ms-4 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('UserViewEvents.php', 'events-submenu'); return false;">
                                <span class="sidebar-text">View Events</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#attendanceSubmenu" onclick="return false;">
                    <i class="fas fa-clipboard-check me-3"></i>
                    <span class="sidebar-text">Attendance</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="attendanceSubmenu">
                    <ul class="nav flex-column ms-4 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('UserAttendance.php', 'attendance-submenu'); return false;">
                                <span class="sidebar-text">Create Attendance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('UserAttendanceReport.php', 'attendance-submenu'); return false;">
                                <span class="sidebar-text">View Attendance</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#announcementsSubmenu" onclick="return false;">
                    <i class="fas fa-bullhorn me-3"></i>
                    <span class="sidebar-text">Announcements</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="announcementsSubmenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('UserViewAnnouncements.php', 'announcements-submenu'); return false;">
                                <span class="sidebar-text">View Announcements</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" onclick="loadPage('UserFeedbackList.php'); return false;">
                    <i class="fas fa-comments me-3"></i>
                    <span class="sidebar-text">Feedback</span>
                </a>
            </li>
            <li class="nav-item mb-3">
                <a href="#" class="nav-link text-white d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#promotionsSubmenu">
                    <i class="fas fa-chart-line me-3"></i>
                    <span class="sidebar-text">Promotions</span>
                    <i class="fas fa-chevron-down ms-auto chevron-animate"></i>
                </a>
                <div class="collapse" id="promotionsSubmenu">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item">
                            <a href="#" class="nav-link text-white" onclick="loadPage('UserViewPromotions.php', 'promotions-submenu'); return false;">
                                <span class="sidebar-text">View Promotions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <!-- Sidebar Toggle Button at the bottom -->
        <div class="sidebar-toggle-btn text-center mt-auto mb-3" style="width: 100%;">
            <button id="userSidebarToggleBtn" class="btn btn-light" style="border-radius: 50%; width: 44px; height: 44px; box-shadow: 0 2px 8px rgba(0,0,0,0.07);">
                <i class="fas fa-angle-left"></i>
            </button>
        </div>
    </nav>

    <!-- Header -->
    <header class="py-2 px-3 px-md-5 d-flex flex-wrap flex-md-nowrap justify-content-between align-items-center shadow-sm bg-white">
        <div class="d-flex align-items-center flex-grow-1 flex-wrap">
            <img src="img/affprotechicon.png" alt="Afro Tech Logo" class="me-3" style="height: 48px;">
            <h4 class="mb-0 fw-semibold ORIGI-TITLE" style="color: navy; font-size: 1.1rem;">
                ASSOCIATION OF FOOD PROCESSING AND TECHNOLOGY STUDENTS
            </h4>
        </div>
        <div class="d-flex align-items-center gap-3 justify-content-end mt-2 mt-md-0" style="position: relative;">
            <!-- Notification Bell (moved left) -->
            <div class="dropdown me-5">
                <button class="btn p-0 position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: none; border: none; outline: none;">
                    <i class="fas fa-bell" style="font-size: 1.7rem; color: #000080;"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount" style="font-size: 0.8rem;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow notification-dropdown-menu" aria-labelledby="notificationDropdown">
                    <li class="dropdown-header fw-bold text-primary" style="font-size: 1.1rem;">Notifications</li>
                    <?php if (empty($recentEvents) && empty($recentAnnouncements)): ?>
                        <li class="notification-empty">No new or upcoming events or announcements.</li>
                    <?php else: ?>
                        <?php foreach ($recentEvents as $event): ?>
                            <li class="notification-item notification-event" style="cursor:pointer;" data-link="UserViewEvents.php">
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
                        <?php foreach ($recentAnnouncements as $ann): ?>
                            <li class="notification-item notification-announcement" style="cursor:pointer;" data-link="UserViewAnnouncements.php">
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
            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="box-shadow:none;">
                    <img src="<?php echo htmlspecialchars($profileImage); ?>"
                         class="profile-img-dropdown"
                         alt="Profile">
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li>
                        <a class="dropdown-item" href="#" onclick="loadPage('UserManageSettings.php'); return false;">
                            <i class="fas fa-cog me-2"></i>Manage Settings
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
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
            <!-- Dashboard Cards and Latest Promotions in a Single Row -->
            <section class="p-4">
                <div class="container-fluid">
                    <!-- First Row: Dashboard Cards -->
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
                    <!-- Second Row: Latest Promotions (up to 3) -->
                    <div class="row g-4 justify-content-center mx-0 mt-4">
                        <?php if (!empty($dashboardPromotions)): ?>
                            <?php foreach (array_slice($dashboardPromotions, 0, 3) as $row): ?>
                                <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center">
                                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>"
                                         class="dashboard-promo-img mb-2"
                                         alt="Promotion Image">
                                    <div class="dashboard-promo-title text-center"><?php echo htmlspecialchars($row['title']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 col-md-4 d-flex flex-column align-items-center justify-content-center">
                                <div class="alert alert-info">No promotions found.</div>
                            </div>
                        <?php endif; ?>
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
                                <div class="mt-2" style="text-align: left;">
                                    <img src="<?php echo htmlspecialchars($latestAnnouncementProfileImg); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; margin-right: 6px; border: 1.5px solid #000080; vertical-align: middle;">
                                    <span class="text-muted">Posted by: <?php echo htmlspecialchars($latestAnnouncement['created_by']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

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

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="eventDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="UserDashboard.js"></script>
    <script>
    // Prevent dropdown from closing when clicking inside
    document.addEventListener('DOMContentLoaded', function() {
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