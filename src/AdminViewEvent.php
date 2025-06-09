<?php
ini_set('session.cookie_path', '/');
session_start();

// Check if user is logged in and is admin
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

// Include database connection
require_once 'DBconnection.php';

// Get all events
$query = "SELECT * FROM afpro_events ORDER BY start_date DESC";
$result = $conn->query($query);
?>

<div id="alert-container"></div>

<section class="p-4">
    <h2 class="fs-4 fw-semibold mb-3">All Events</h2>
    <div class="bg-white p-4 shadow-sm rounded">
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 g-4" id="event-list">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col event-card" data-event-id="<?php echo $row['event_id']; ?>">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0 text-white"><?php echo htmlspecialchars($row['title']); ?></h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">
                                    <i class="fa fa-calendar me-2"></i>
                                    <?php echo date('M d, Y', strtotime($row['start_date'])); ?> - <?php echo date('M d, Y', strtotime($row['end_date'])); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fa fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </p>
                                <p class="mb-2">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                            </div>
                            <div class="card-footer d-flex gap-2 justify-content-end bg-white border-0">
                                <button type="button" class="btn btn-sm btn-warning edit-event" data-event='<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-event" data-bs-toggle="modal" data-bs-target="#deleteEventModal" data-event-id="<?php echo $row['event_id']; ?>" data-event-title="<?php echo htmlspecialchars(addslashes($row['title'])); ?>">
                                    <i class="fas fa-trash"></i> Delete
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
                <p>There are no events scheduled at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the event <strong id="deleteEventTitle"></strong>?</p>
                <input type="hidden" id="deleteEventId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEventBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEventForm">
                <div class="modal-body">
                    <input type="hidden" id="editEventId" name="event_id">
                    <div class="mb-3">
                        <label for="editEventTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="editEventTitle" name="eventTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editEventDescription" name="eventDescription"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="editEventStartDate" class="form-label">Start Date</label>
                        <input type="datetime-local" class="form-control" id="editEventStartDate" name="eventStartDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventEndDate" class="form-label">End Date</label>
                        <input type="datetime-local" class="form-control" id="editEventEndDate" name="eventEndDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEventLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="editEventLocation" name="eventLocation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Event Modal
    document.querySelectorAll('.edit-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventData = JSON.parse(this.getAttribute('data-event'));
            document.getElementById('editEventId').value = eventData.event_id;
            document.getElementById('editEventTitle').value = eventData.title;
            document.getElementById('editEventDescription').value = eventData.description || '';
            document.getElementById('editEventStartDate').value = formatDateForInput(eventData.start_date);
            document.getElementById('editEventEndDate').value = formatDateForInput(eventData.end_date);
            document.getElementById('editEventLocation').value = eventData.location || '';
        });
    });

    // Delete Event Modal
    document.querySelectorAll('.delete-event').forEach(button => {
        button.addEventListener('click', function() {
            const eventId = this.getAttribute('data-event-id');
            const eventTitle = this.getAttribute('data-event-title');
            document.getElementById('deleteEventId').value = eventId;
            document.getElementById('deleteEventTitle').textContent = eventTitle;
        });
    });

    document.getElementById('confirmDeleteEventBtn').addEventListener('click', function() {
        const eventId = document.getElementById('deleteEventId').value;
        const eventCard = document.querySelector(`.event-card[data-event-id="${eventId}"]`);

        fetch('AdminDeleteEvent.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'event_id=' + encodeURIComponent(eventId)
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deleteEventModal'));
            modal.hide();
            if (data.success) {
                showAlert('Event deleted successfully!', 'success');
                if (eventCard) {
                    eventCard.remove(); // Remove the specific event card from the DOM
                }
                // Check if there are any events left
                const remainingEvents = document.querySelectorAll('.event-card').length;
                if (remainingEvents === 0) {
                    document.getElementById('event-list').innerHTML = `
                        <div class="text-center p-4">
                            <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                            <h3>No Events Found</h3>
                            <p>There are no events scheduled at the moment.</p>
                        </div>
                    `;
                }
            } else {
                showAlert(data.message || 'Error deleting event', 'danger');
            }
        })
        .catch(error => {
            showAlert('Error deleting event: ' + error.message, 'danger');
        });
    });

    document.getElementById('editEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('AdminUpdateEvent.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editEventModal'));
            modal.hide();
            if (data.success) {
                showAlert('Event updated successfully!', 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert(data.message || 'Error updating event', 'danger');
            }
        })
        .catch(error => {
            showAlert('Error updating event: ' + error.message, 'danger');
        });
    });

    // Helper function to format date for input
    function formatDateForInput(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const offset = date.getTimezoneOffset();
        const localDate = new Date(date.getTime() - (offset * 60000));
        return localDate.toISOString().slice(0, 16);
    }

    // Helper function to show alerts
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        if (!alertContainer) return;

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        alertContainer.prepend(alert);
        setTimeout(() => alert.remove(), 3000);
    }
});
</script>

<?php $conn->close(); ?>