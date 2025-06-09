<?php
require_once 'DBconnection.php';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="promotions_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Promotion ID', 'Title', 'Description', 'Price', 'Start Date', 'End Date', 'Location']);

$result = $conn->query('SELECT promotion_id, title, description, price, start_date, end_date, location FROM afpro_promotion');
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
$conn->close();
exit; 