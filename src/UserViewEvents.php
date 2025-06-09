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
    <h2 class="fs-4 fw-semibold mb-3">All Events</h2>
    <div class="bg-white p-5 shadow-sm rounded" style="max-width: 1300px; margin: 0 auto;">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center align-items-stretch">
                <?php while($event = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-6 mb-4 d-flex justify-content-center">
                        <div class="card h-100 shadow-sm" style="min-width: 420px; max-width: 560px; min-height: 250px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div class="card-header text-white text-center" style="background: #000080; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 0.5rem 0;">
                                <h5 class="card-title mb-0 fw-bold text-center" style="font-size: 1.5rem; letter-spacing: 0.5px; text-transform: uppercase; color: #fff;">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </h5>
                            </div>
                            <div class="card-body" style="padding: 1rem;">
                                <div class="d-flex justify-content-center align-items-center mb-3" style="font-size: 2.8rem; color: #001f5b;">
                                    <i class="fa fa-calendar-alt"></i>
                                </div>
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
                                <p class="mb-2 event-description" style="word-break: break-word; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center p-4">
                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                <h3>No Events Found</h3>
                <p>There are no events scheduled at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $conn->close(); ?> 