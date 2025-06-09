<?php
session_start();
require_once 'DBconnection.php';

date_default_timezone_set('Asia/Manila');

// Check if the user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: Login.php");
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST['message']);
    $created_by = $_SESSION['fullname']; // Get the current admin's full name
    $created_at = date('Y-m-d H:i:s');

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO afpro_announcement (message, created_by, created_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $message, $created_by, $created_at);

    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Announcement Created!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating announcement: ' . $conn->error]);
        }
        $stmt->close();
        $conn->close();
        exit;
    } else {
        if ($stmt->execute()) {
            header("Location: AdminViewAnnouncements.php?msg=Announcement+created+successfully");
            exit;
        } else {
            $error = "Error creating announcement: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<section class="p-4">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-md-4 col-lg-5 col-xl-5 mx-auto">
            <div class="card shadow p-4 announcement-card">
                <div class="card-body">
                    <h3 class="text-center mb-4">Create Announcement</h3>
                    <form id="announcement-create-form" method="POST" action="CreateAnnouncement.php">
                        <div id="announcement-alert-container"></div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Announcement Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Created By</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['fullname']); ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Created At</label>
                            <input type="text" class="form-control" id="created_at" name="created_at" value="<?php echo date('F j, Y g:i A'); ?>" readonly>
                        </div>

                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Create Announcement
                            </button>
                            <a href="#" onclick="loadPage('AdminViewAnnouncements.php', 'announcements-submenu')" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $conn->close(); ?> 