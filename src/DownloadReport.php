<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}

require_once 'DBconnection.php';

// Validate input parameters
$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? '';
$period = $_GET['period'] ?? 'All';

if (!$type || !$format || !in_array($type, ['events', 'promotions', 'attendance', 'announcements']) || !in_array($format, ['pdf', 'csv', 'txt'])) {
    http_response_code(400);
    die('Invalid request parameters.');
}

date_default_timezone_set('Asia/Manila');

// Function to sanitize output for CSV and TXT
function sanitizeOutput($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $type . '_report.csv"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    
    switch ($type) {
        case 'events':
            fputcsv($output, ['Event ID', 'Title', 'Start Date', 'End Date', 'Location']);
            $result = $conn->query('SELECT event_id, title, location, start_date, end_date FROM afpro_events');
            while ($row = $result->fetch_assoc()) {
                $start = ($row['start_date'] && $row['start_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d 08:00 AM', strtotime($row['start_date'] . ' 08:00:00')) : '';
                $end = ($row['end_date'] && $row['end_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d 08:00 PM', strtotime($row['end_date'] . ' 20:00:00')) : '';
                fputcsv($output, [
                    sanitizeOutput($row['event_id']),
                    sanitizeOutput($row['title']),
                    $start,
                    $end,
                    sanitizeOutput($row['location'])
                ]);
            }
            break;
        case 'promotions':
            fputcsv($output, ['Promotion ID', 'Title', 'Description', 'Price', 'Start Date', 'End Date', 'Location']);
            $result = $conn->query('SELECT promotion_id, title, description, price, start_date, end_date, location FROM afpro_promotion');
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    sanitizeOutput($row['promotion_id']),
                    sanitizeOutput($row['title']),
                    sanitizeOutput($row['description']),
                    sanitizeOutput($row['price']),
                    sanitizeOutput($row['start_date']),
                    sanitizeOutput($row['end_date']),
                    sanitizeOutput($row['location'])
                ]);
            }
            break;
        case 'attendance':
            fputcsv($output, ['Attendance ID', 'Student ID', 'Event Title', 'Check In Time', 'Check Out Time', 'Status', 'Period']);
            $query = "
                SELECT a.attendance_id, u.student_id, e.title, a.check_in_time, a.check_out_time, a.status, a.period
                FROM afpro_attendance a
                JOIN afpro_users u ON a.user_id = u.user_id
                JOIN afpro_events e ON a.event_id = e.event_id
            ";
            if ($period !== 'All') {
                $query .= " WHERE LOWER(TRIM(a.period)) = ?";
                $stmt = $conn->prepare($query);
                $periodLower = strtolower(trim($period));
                $stmt->bind_param("s", $periodLower);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($query);
            }
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    sanitizeOutput($row['attendance_id']),
                    sanitizeOutput($row['student_id']),
                    sanitizeOutput($row['title']),
                    sanitizeOutput($row['check_in_time'] ? date('Y-m-d H:i:s', strtotime($row['check_in_time'])) : ''),
                    sanitizeOutput($row['check_out_time'] ? date('Y-m-d H:i:s', strtotime($row['check_out_time'])) : ''),
                    sanitizeOutput($row['status']),
                    sanitizeOutput($row['period'])
                ]);
            }
            break;
        case 'announcements':
            fputcsv($output, ['Announcement ID', 'Message', 'Created By', 'Created At']);
            $result = $conn->query('SELECT announcement_id, message, created_by, created_at FROM afpro_announcement');
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, [
                    sanitizeOutput($row['announcement_id']),
                    sanitizeOutput($row['message']),
                    sanitizeOutput($row['created_by']),
                    sanitizeOutput($row['created_at'])
                ]);
            }
            break;
    }
    fclose($output);
    $conn->close();
    exit;
}

if ($format === 'txt') {
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $type . '_report.txt"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    switch ($type) {
        case 'events':
            echo "Event ID\tTitle\tStart Date\tEnd Date\tLocation\n";
            $result = $conn->query('SELECT event_id, title, location, start_date, end_date FROM afpro_events');
            while ($row = $result->fetch_assoc()) {
                $start = ($row['start_date'] && $row['start_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d 08:00 AM', strtotime($row['start_date'] . ' 08:00:00')) : '';
                $end = ($row['end_date'] && $row['end_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d 08:00 PM', strtotime($row['end_date'] . ' 20:00:00')) : '';
                echo sanitizeOutput($row['event_id']) . "\t" . sanitizeOutput($row['title']) . "\t" . $start . "\t" . $end . "\t" . sanitizeOutput($row['location']) . "\n";
            }
            break;
        case 'promotions':
            echo "Promotion ID\tTitle\tDescription\tPrice\tStart Date\tEnd Date\tLocation\n";
            $result = $conn->query('SELECT promotion_id, title, description, price, start_date, end_date, location FROM afpro_promotion');
            while ($row = $result->fetch_assoc()) {
                echo sanitizeOutput($row['promotion_id']) . "\t" . sanitizeOutput($row['title']) . "\t" . sanitizeOutput($row['description']) . "\t" . sanitizeOutput($row['price']) . "\t" . sanitizeOutput($row['start_date']) . "\t" . sanitizeOutput($row['end_date']) . "\t" . sanitizeOutput($row['location']) . "\n";
            }
            break;
        case 'attendance':
            echo "Attendance ID\tStudent ID\tEvent Title\tCheck In Time\tCheck Out Time\tStatus\tPeriod\n";
            $query = "
                SELECT a.attendance_id, u.student_id, e.title, a.check_in_time, a.check_out_time, a.status, a.period
                FROM afpro_attendance a
                JOIN afpro_users u ON a.user_id = u.user_id
                JOIN afpro_events e ON a.event_id = e.event_id
            ";
            if ($period !== 'All') {
                $query .= " WHERE LOWER(TRIM(a.period)) = ?";
                $stmt = $conn->prepare($query);
                $periodLower = strtolower(trim($period));
                $stmt->bind_param("s", $periodLower);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($query);
            }
            while ($row = $result->fetch_assoc()) {
                echo sanitizeOutput($row['attendance_id']) . "\t" . sanitizeOutput($row['student_id']) . "\t" . sanitizeOutput($row['title']) . "\t" . (isset($row['check_in_time']) ? date('Y-m-d H:i:s', strtotime($row['check_in_time'])) : '') . "\t" . (isset($row['check_out_time']) ? date('Y-m-d H:i:s', strtotime($row['check_out_time'])) : '') . "\t" . sanitizeOutput($row['status']) . "\t" . sanitizeOutput($row['period']) . "\n";
            }
            break;
        case 'announcements':
            echo "Announcement ID\tMessage\tCreated By\tCreated At\n";
            $result = $conn->query('SELECT announcement_id, message, created_by, created_at FROM afpro_announcement');
            while ($row = $result->fetch_assoc()) {
                echo sanitizeOutput($row['announcement_id']) . "\t" . sanitizeOutput($row['message']) . "\t" . sanitizeOutput($row['created_by']) . "\t" . sanitizeOutput($row['created_at']) . "\n";
            }
            break;
    }
    $conn->close();
    exit;
}

if ($format === 'pdf') {
    require_once 'fpdf/fpdf.php';
    $pdf = new FPDF('P', 'mm', 'A4'); // Portrait mode for better fit
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 14);
    $title = ucfirst($type) . ' Report';
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 10);
    switch ($type) {
        case 'events':
            $colWidths = [17, 40, 60, 40, 40];
            $rowHeight = 12;
            $headers = ['ID', 'Title', 'Location', 'Start Date', 'End Date'];
            // Header
            for ($i = 0; $i < count($headers); $i++) {
                $pdf->Cell($colWidths[$i], $rowHeight, $headers[$i], 1, 0, 'C');
            }
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 10);
            $result = $conn->query('SELECT event_id, title, location, start_date, end_date FROM afpro_events');
            while ($row = $result->fetch_assoc()) {
                $start = ($row['start_date'] && $row['start_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($row['start_date'])) . ' 08:00 AM' : '';
                $end = ($row['end_date'] && $row['end_date'] !== '0000-00-00 00:00:00') ? date('Y-m-d', strtotime($row['end_date'])) . ' 08:00 PM' : '';
                $pdf->Cell($colWidths[0], $rowHeight, $row['event_id'], 1);
                $pdf->Cell($colWidths[1], $rowHeight, mb_strimwidth($row['title'], 0, 20, '...'), 1);
                $pdf->Cell($colWidths[2], $rowHeight, mb_strimwidth($row['location'], 0, 60, '...'), 1);
                $pdf->Cell($colWidths[3], $rowHeight, $start, 1);
                $pdf->Cell($colWidths[4], $rowHeight, $end, 1);
                $pdf->Ln();
            }
            break;
        case 'promotions':
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(40, 10, 'Title', 1);
            $pdf->Cell(50, 10, 'Description', 1);
            $pdf->Cell(20, 10, 'Price', 1);
            $pdf->Cell(30, 10, 'Start Date', 1);
            $pdf->Cell(30, 10, 'End Date', 1);
            $pdf->Cell(30, 10, 'Location', 1);
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 12);
            $result = $conn->query('SELECT promotion_id, title, description, price, start_date, end_date, location FROM afpro_promotion');
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(20, 10, $row['promotion_id'], 1);
                $pdf->Cell(40, 10, $row['title'], 1);
                $pdf->Cell(50, 10, $row['description'], 1);
                $pdf->Cell(20, 10, $row['price'], 1);
                $pdf->Cell(30, 10, date('M d, Y', strtotime($row['start_date'])), 1);
                $pdf->Cell(30, 10, date('M d, Y', strtotime($row['end_date'])), 1);
                $pdf->Cell(30, 10, $row['location'], 1);
                $pdf->Ln();
            }
            break;
        case 'attendance':
            $pdf->Cell(20, 10, 'ID', 1);
            $pdf->Cell(30, 10, 'Student ID', 1);
            $pdf->Cell(40, 10, 'Event Title', 1);
            $pdf->Cell(35, 10, 'Check In', 1);
            $pdf->Cell(35, 10, 'Check Out', 1);
            $pdf->Cell(20, 10, 'Status', 1);
            $pdf->Cell(20, 10, 'Period', 1);
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 12);
            $query = "
                SELECT a.attendance_id, u.student_id, e.title, a.check_in_time, a.check_out_time, a.status, a.period
                FROM afpro_attendance a
                JOIN afpro_users u ON a.user_id = u.user_id
                JOIN afpro_events e ON a.event_id = e.event_id
            ";
            if ($period !== 'All') {
                $query .= " WHERE LOWER(TRIM(a.period)) = ?";
                $stmt = $conn->prepare($query);
                $periodLower = strtolower(trim($period));
                $stmt->bind_param("s", $periodLower);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $conn->query($query);
            }
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(20, 10, $row['attendance_id'], 1);
                $pdf->Cell(30, 10, $row['student_id'], 1);
                $pdf->Cell(40, 10, $row['title'], 1);
                $pdf->Cell(35, 10, $row['check_in_time'] ? date('Y-m-d H:i:s', strtotime($row['check_in_time'])) : '', 1);
                $pdf->Cell(35, 10, $row['check_out_time'] ? date('Y-m-d H:i:s', strtotime($row['check_out_time'])) : '', 1);
                $pdf->Cell(20, 10, $row['status'], 1);
                $pdf->Cell(20, 10, $row['period'], 1);
                $pdf->Ln();
            }
            break;
        case 'announcements':
            $pdf->Cell(30, 10, 'ID', 1);
            $pdf->Cell(100, 10, 'Message', 1);
            $pdf->Cell(30, 10, 'Created By', 1);
            $pdf->Cell(30, 10, 'Created At', 1);
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 12);
            $result = $conn->query('SELECT announcement_id, message, created_by, created_at FROM afpro_announcement');
            while ($row = $result->fetch_assoc()) {
                $pdf->Cell(30, 10, $row['announcement_id'], 1);
                $pdf->Cell(100, 10, $row['message'], 1);
                $pdf->Cell(30, 10, $row['created_by'], 1);
                $pdf->Cell(30, 10, $row['created_at'], 1);
                $pdf->Ln();
            }
            break;
    }
    $conn->close();
    $pdf->Output('D', $type . '_report.pdf');
    exit;
}