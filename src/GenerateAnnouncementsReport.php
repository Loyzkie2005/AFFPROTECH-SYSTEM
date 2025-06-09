<?php
require_once 'DBconnection.php';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="announcements_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Announcement ID', 'Message', 'Created By', 'Created At']);

$result = $conn->query('SELECT announcement_id, message, created_by, created_at FROM afpro_announcement');
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
$conn->close();
exit; 