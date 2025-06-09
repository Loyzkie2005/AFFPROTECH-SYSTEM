<?php
ini_set('session.cookie_path', '/');
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    // Check if this is an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        // For AJAX requests, return a message instead of redirecting
        echo '<div class="alert alert-danger m-3">
                <h4 class="alert-heading">Session Expired</h4>
                <p>Your session has expired or you do not have permission to access this page.</p>
                <hr>
                <p class="mb-0">Please <a href="Login.php">log in</a> again.</p>
              </div>';
        exit;
    } else {
        // For direct requests, redirect to login page
        header("Location: Login.php");
        exit;
    }
}

// Include database connection
require_once 'DBconnection.php';

// Fetch feedback with event title
$feedbacks = [];
$query = "SELECT f.feedback_id, f.user_id, f.message, u.first_name, u.last_name, e.title AS event_title, f.rating
          FROM afpro_feedback f
          JOIN afpro_users u ON f.user_id = u.user_id
          LEFT JOIN afpro_events e ON f.event_id = e.event_id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
} else {
    // Log the error for debugging (optional)
    error_log("Query failed: " . $conn->error);
}

// Calculate average rating
$average_rating = null;
$total_ratings = 0;
$rating_sum = 0;
foreach ($feedbacks as $feedback) {
    if (isset($feedback['rating']) && is_numeric($feedback['rating']) && $feedback['rating'] > 0) {
        $rating_sum += (int)$feedback['rating'];
        $total_ratings++;
    }
}
if ($total_ratings > 0) {
    $average_rating = round($rating_sum / $total_ratings, 2);
}
?>

<style>
.table thead th {
    background-color: #000080 !important; /* Navy blue */
    color: #fff !important;
}
.table tbody td {
    vertical-align: middle;
}
.fa-star.star, .fa-star-half-alt.star {
    color: #ffd600;
}
.fa-star.star-empty {
    color: #ccc;
}
</style>

<div class="container p-4">
    <h2 class="fs-4 fw-semibold mb-4 text-center">User Feedback</h2>
    <?php if ($average_rating !== null): ?>
        <div class="mb-3 text-center">
            <span class="fw-semibold">Average Rating:</span>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="fa-star fa<?= $i <= round($average_rating) ? 's star' : 'r star star-empty' ?>"></i>
            <?php endfor; ?>
            <span class="ms-2"><?php echo $average_rating; ?> / 5 (<?php echo $total_ratings; ?> ratings)</span>
        </div>
    <?php endif; ?>
    <?php if (empty($feedbacks)): ?>
        <div class="alert alert-info text-center">No feedback available at the moment.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Event</th>
                        <th>Message</th>
                        <th>Rating</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($feedbacks as $feedback): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($feedback['event_title'] ?? 'N/A'); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></td>
                        <td>
                            <?php
                            $rating = isset($feedback['rating']) ? (int)$feedback['rating'] : 0;
                            if ($rating > 0) {
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star" style="color: #ffd600;"></i>';
                                    } else {
                                        echo '<i class="far fa-star" style="color: #ccc;"></i>';
                                    }
                                }
                                echo '<span class="ms-2">' . $rating . ' / 5</span>';
                            } else {
                                for ($i = 1; $i <= 5; $i++) {
                                    echo '<i class="far fa-star" style="color: #ccc;"></i>';
                                }
                                echo '<span class="ms-2 text-muted">No rating</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $conn->close(); ?>