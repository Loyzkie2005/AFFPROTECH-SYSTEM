<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: Login.php");
    exit;
}

// Include database connection
require_once 'DBconnection.php';

$userId = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;

// Get all events for dropdown
$eventQuery = "SELECT event_id, title FROM afpro_events ORDER BY start_date DESC";
$eventResult = $conn->query($eventQuery);

// Set timezone to Philippine time
ini_set('date.timezone', 'Asia/Manila');
?>

<section class="p-4 d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card shadow-lg p-4" style="max-width: 800px; width: 100%; border-radius: 18px;">
        <div id="attendance-alert"></div>
        <h2 class="mb-4 text-center fw-bold">Create Attendance</h2>
        <form action="javascript:void(0);" method="POST" id="create-attendance-form">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
            <div class="mb-3">
                <label for="event_id" class="form-label">Event</label>
                <select class="form-control" id="event_id" name="event_id" required>
                    <option value="">Select Event</option>
                    <?php if ($eventResult && $eventResult->num_rows > 0): ?>
                        <?php while($event = $eventResult->fetch_assoc()): ?>
                            <option value="<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['title']); ?></option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No upcoming events</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="check_in_time" class="form-label">Check-In Time</label>
                <input type="datetime-local" class="form-control" id="check_in_time" name="check_in_time" required>
            </div>
            <div class="mb-3">
                <label for="check_out_time" class="form-label">Check-Out Time</label>
                <input type="datetime-local" class="form-control" id="check_out_time" name="check_out_time">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Submit Attendance</button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('create-attendance-form');
    const alertDiv = document.getElementById('attendance-alert');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            fetch('AttendanceCreate.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Network error: ${response.status} - ${text}`);
                    });
                }
                return response.text().then(text => {
                    if (!text) {
                        throw new Error('Empty response received');
                    }
                    try {
                        return JSON.parse(text);
                    } catch (error) {
                        throw new Error(`Invalid JSON: ${text}`);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    alertDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    form.reset();
                } else {
                    alertDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
                alertDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => { alertDiv.innerHTML = ''; }, 2500);
            })
            .catch(error => {
                alertDiv.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                setTimeout(() => { alertDiv.innerHTML = ''; }, 2500);
                console.error('Fetch error:', error);
            });
        });
    }
});
</script>

<?php 
$conn->close(); 
?>