<?php
date_default_timezone_set('Asia/Manila');
require_once 'fpdf/fpdf.php';
require_once 'DBconnection.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Events Report', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(20, 10, 'ID', 1);
$pdf->Cell(60, 10, 'Title', 1);
$pdf->Cell(40, 10, 'Location', 1);
$pdf->Cell(35, 10, 'Start Date', 1);
$pdf->Cell(35, 10, 'End Date', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
$result = $conn->query('SELECT event_id, title, location, start_date, end_date FROM afpro_events');
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 10, $row['event_id'], 1);
    $pdf->Cell(60, 10, $row['title'], 1);
    $pdf->Cell(40, 10, $row['location'], 1);
    $pdf->Cell(35, 10, date('M d, Y h:i A', strtotime($row['start_date'])), 1);
    $pdf->Cell(35, 10, date('M d, Y h:i A', strtotime($row['end_date'])), 1);
    $pdf->Ln();
}
$pdf->Output('D', 'events_report.pdf');
$conn->close();
exit; 