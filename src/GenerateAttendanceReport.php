<?php
require_once 'DBconnection.php';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="attendance_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Attendance ID', 'User ID', 'Event ID', 'Check In Time', 'Check Out Time', 'Status', 'Period']);

$result = $conn->query('SELECT attendance_id, user_id, event_id, check_in_time, check_out_time, status, period FROM afpro_attendance');
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
$conn->close();
exit; 