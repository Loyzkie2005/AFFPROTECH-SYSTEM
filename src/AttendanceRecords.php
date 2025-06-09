<?php
ini_set('session.cookie_path', '/');
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: Login.php");
    exit;
}
require_once 'DBconnection.php';

// Fetch attendance records with student and event details
$query = "
    SELECT a.attendance_id, a.user_id, u.student_id, e.event_id, e.title, a.check_in_time, a.check_out_time, a.status, a.period
    FROM afpro_attendance a
    JOIN afpro_users u ON a.user_id = u.user_id
    JOIN afpro_events e ON a.event_id = e.event_id
";
$result = $conn->query($query);

// Collect rows and count metrics
$rows = [];
$totalAttendance = $totalPresent = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] !== 'Pending') {
            $rows[] = $row;
            $totalAttendance++;
            if ($row['status'] === 'Present') $totalPresent++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-metric {
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 24px 0;
            min-width: 180px;
            text-align: center;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .card-metric .value {
            font-size: 2.2rem;
            font-weight: bold;
            margin-top: 8px;
        }
        .card-metric.present { background: #d4edda; color: #155724; }
        .card-metric.total { background: #e3eaff; color: #00205b; }
        .attendance-table th, .attendance-table td {
            vertical-align: middle;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 2px;
        }
        .badge {
            font-size: 0.875rem;
            padding: 0.5em 0.8em;
        }
    </style>
</head>
<body>
<section class="p-4" style="min-height: 100vh; background: #f8f9fa;">
    <div class="container" style="max-width: 1200px;">
        <h2 class="mb-4 text-center fw-bold">Attendance Records</h2>
        <div class="d-flex flex-wrap gap-4 justify-content-center mb-4">
            <div class="card-metric total">
                Total Attendance
                <div class="value"><?php echo $totalAttendance; ?></div>
            </div>
            <div class="card-metric present">
                Total Present
                <div class="value"><?php echo $totalPresent; ?></div>
            </div>
        </div>
        <div class="attendance-table-container" style="width:100%;">
            <table class="table table-striped attendance-table" style="width:100%; background: #fff; border-radius: 12px; overflow: hidden;">
                <thead>
                    <tr>
                        <th>Attendance ID</th>
                        <th>Student ID</th>
                        <th>Event</th>
                        <th>Check-In Time</th>
                        <th>Check-Out Time</th>
                        <th>Status</th>
                        <th>Period</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center">No attendance records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr data-attendance-id="<?= $row['attendance_id'] ?>">
                                <td><?= htmlspecialchars($row['attendance_id']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= $row['check_in_time'] ? (new DateTime($row['check_in_time']))->format('Y-m-d H:i') : 'N/A' ?></td>
                                <td><?= $row['check_out_time'] ? (new DateTime($row['check_out_time']))->format('Y-m-d H:i') : 'N/A' ?></td>
                                <td><span style="background:#28a745;color:#fff;padding:6px 16px;border-radius:8px;display:inline-block;min-width:70px;text-align:center;"><?= htmlspecialchars($row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['period']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm edit-attendance"
                                            data-id="<?= htmlspecialchars($row['attendance_id']) ?>"
                                            data-user-id="<?= htmlspecialchars($row['user_id']) ?>"
                                            data-event-id="<?= htmlspecialchars($row['event_id']) ?>"
                                            data-check-in="<?= htmlspecialchars($row['check_in_time']) ?>"
                                            data-check-out="<?= htmlspecialchars($row['check_out_time']) ?>"
                                            >
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm delete-attendance" 
                                            data-id="<?= htmlspecialchars($row['attendance_id']) ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

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

<!-- Delete Attendance Modal -->
<div class="modal fade" id="deleteAttendanceModal" tabindex="-1" aria-labelledby="deleteAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <p class="delete-message" id="deleteAttendanceModalLabel">Are you sure you want to delete this attendance record?</p>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAttendanceBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Attendance
    document.querySelectorAll('.edit-attendance').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('updateAttendanceModal'));
            document.getElementById('attendanceId').value = this.dataset.id;
            document.getElementById('update_user_id').value = this.dataset.userId;
            document.getElementById('update_event_id').value = this.dataset.eventId;
            document.getElementById('update_check_in_time').value = this.dataset.checkIn ? this.dataset.checkIn.replace(' ', 'T') : '';
            document.getElementById('update_check_out_time').value = this.dataset.checkOut ? this.dataset.checkOut.replace(' ', 'T') : '';
            document.getElementById('update_status').value = this.dataset.status;
            modal.show();
        });
    });

    // Delete Attendance
    document.querySelectorAll('.delete-attendance').forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
            document.getElementById('confirmDeleteAttendanceBtn').dataset.id = this.dataset.id;
            modal.show();
        });
    });

    document.getElementById('confirmDeleteAttendanceBtn').addEventListener('click', function() {
        const attendanceId = this.dataset.id;
        const formData = new FormData();
        formData.append('attendance_id', attendanceId);

        fetch('AttendanceDelete.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
            alert(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the attendance record');
        })
        .finally(() => {
            bootstrap.Modal.getInstance(document.getElementById('deleteAttendanceModal')).hide();
        });
    });

    // Update Attendance Form Submit
    document.getElementById('attendance-update-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('AttendanceUpdate.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
            alert(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the attendance record');
        });
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>