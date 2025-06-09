<?php
session_start();
require_once 'DBconnection.php';

// Period filter
$period = isset($_GET['period']) ? $_GET['period'] : 'All';
$period = trim($period);
$periodOptions = ['All', 'Day', 'Afternoon'];

// Fetch attendance records with student and event details
$query = "
    SELECT a.attendance_id, a.user_id, u.student_id, e.event_id, e.title, a.check_in_time, a.check_out_time, a.status, a.period
    FROM afpro_attendance a
    JOIN afpro_users u ON a.user_id = u.user_id
    JOIN afpro_events e ON a.event_id = e.event_id
    WHERE a.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

// Filter and count
$rows = [];
$totalAttendance = $totalPresent = $totalPending = 0;
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($period === 'All' || strtolower(trim($row['period'])) === strtolower(trim($period))) {
            $rows[] = $row;
            $totalAttendance++;
            if ($row['status'] === 'Present') $totalPresent++;
            if ($row['status'] === 'Pending') $totalPending++;
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
        .card-metric.pending { background: #fff3cd; color: #856404; }
        .card-metric.present { background: #d4edda; color: #155724; }
        .card-metric.total { background: #e3eaff; color: #00205b; }
    </style>
</head>
<body>
<section class="p-4" style="min-height: 100vh; background: #f8f9fa;">
    <div class="container" style="max-width: 1200px;">
        <h2 class="mb-4 text-center fw-bold">Attendance Records</h2>
        <div class="d-flex flex-wrap gap-4 justify-content-center mb-4">
            <!-- Removed status cards -->
        </div>
        <div class="d-flex justify-content-end mb-3">
            <!-- Filter form removed -->
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center">No attendance records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['attendance_id']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= $row['check_in_time'] ? (new DateTime($row['check_in_time']))->format('Y-m-d H:i') : 'N/A' ?></td>
                                <td><?= $row['check_out_time'] ? (new DateTime($row['check_out_time']))->format('Y-m-d H:i') : 'N/A' ?></td>
                                <td><span style="background:#28a745;color:#fff;padding:6px 16px;border-radius:8px;display:inline-block;min-width:70px;text-align:center;"><?= htmlspecialchars($row['status']) ?></span></td>
                                <td><?= htmlspecialchars($row['period']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Update Attendance Modal (aligned with Admin.php) -->
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

<!-- Delete Attendance Modal (aligned with Admin.php) -->
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('period').addEventListener('change', function() {
    this.form.submit();
});

// Edit Attendance
document.querySelectorAll('.edit-attendance').forEach(btn => {
    btn.onclick = function() {
        document.getElementById('attendanceId').value = this.dataset.id;
        document.getElementById('update_user_id').value = this.dataset.userId;
        document.getElementById('update_event_id').value = this.dataset.eventId;
        document.getElementById('update_check_in_time').value = this.dataset.checkIn ? this.dataset.checkIn.replace(' ', 'T') : '';
        document.getElementById('update_check_out_time').value = this.dataset.checkOut ? this.dataset.checkOut.replace(' ', 'T') : '';
        document.getElementById('update_status').value = this.dataset.status;
        var modal = new bootstrap.Modal(document.getElementById('updateAttendanceModal'));
        modal.show();
    };
});

document.getElementById('attendance-update-form').onsubmit = function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    fetch('AttendanceUpdate.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the attendance record.');
    });
};

// Delete Attendance
document.querySelectorAll('.delete-attendance').forEach(btn => {
    btn.onclick = function() {
        var attendanceId = this.dataset.id;
        var modal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
        modal.show();

        document.getElementById('confirmDeleteAttendanceBtn').onclick = function() {
            fetch('AttendanceDelete.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'attendance_id=' + attendanceId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the attendance record.');
            });
        };
    };
});
</script>
</body>
</html>
<?php $conn->close(); ?>