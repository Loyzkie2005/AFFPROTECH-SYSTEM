<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: Login.php");
    exit;
}

// Include database connection
require_once 'DBconnection.php';

// Get all events
$query = "SELECT * FROM afpro_events ORDER BY start_date DESC";
$result = $conn->query($query);
?>

<section class="p-4">
    <h2 class="fs-4 fw-semibold mb-3">Select Event for Feedback</h2>
    <div class="bg-white p-4 shadow-sm rounded">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4">
                <?php while($event = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-header text-white text-center" style="background: #001f5b;">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <i class="fa fa-calendar me-2"></i>
                                    <?php
                                    $startDate = strtotime($event['start_date']);
                                    $endDate = strtotime($event['end_date']);
                                    if (date('Y-m-d', $startDate) === date('Y-m-d', $endDate)) {
                                        // Same day: show one date
                                        echo date('M d, Y', $startDate);
                                    } else {
                                        // Different days: show both dates
                                        echo date('M d, Y', $startDate) . ' - ' . date('M d, Y', $endDate);
                                    }
                                    ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fa fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <button class="btn w-100" style="background: #001f5b; color: #fff;" onclick="loadPage('UserFeedback.php?event_id=<?php echo $event['event_id']; ?>', 'feedback-submenu')">
                                    Provide Feedback
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center p-4">
                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                <h3>No Events Found</h3>
                <p>There are no events available for feedback at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $conn->close(); ?> 