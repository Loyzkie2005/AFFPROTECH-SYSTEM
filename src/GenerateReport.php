<?php
session_start();
require_once 'DBconnection.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: Login.php");
    exit;
}

// Get all events for potential future use (e.g., filtering reports)
$events = [];
$result = $conn->query("SELECT event_id, title FROM afpro_events ORDER BY start_date DESC");
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

// Get user's reports
$user_id = $_SESSION['user_id'];
$reports = [];
$result = $conn->query("SELECT r.*, e.title FROM afpro_reports r JOIN afpro_events e ON r.event_id = e.event_id WHERE r.user_id = $user_id ORDER BY r.generated_at DESC");
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports - AFPROTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .report-section {
            padding: 40px 0;
            text-align: center;
        }
        .report-section h2 {
            color: #000080;
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 40px;
        }
        .report-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 6px solid #000080;
            padding: 28px 20px 24px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            min-height: 240px;
            transition: box-shadow 0.2s, transform 0.2s;
            position: relative;
            margin-bottom: 10px;
        }
        .report-card:hover {
            box-shadow: 0 6px 24px rgba(0,0,0,0.13);
            transform: translateY(-4px) scale(1.02);
        }
        .report-card .icon {
            font-size: 2.2rem;
            color: #000080;
            margin-bottom: 12px;
            background: #e6eaff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .report-card .card-title {
            font-size: 1.18rem;
            font-weight: 700;
            color: #000080;
            margin-bottom: 6px;
            margin-top: 2px;
        }
        .report-card .card-desc {
            color: #444;
            font-size: 1.01rem;
            margin-bottom: 18px;
            min-height: 38px;
        }
        .report-card .btn {
            width: 100%;
            font-size: 1.08rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 0;
            background: #000080;
            border: none;
            color: #fff;
            transition: background 0.18s;
        }
        .report-card .btn:hover {
            background: #2323a6;
            color: #fff;
        }
        @media (max-width: 900px) {
            .report-card { min-width: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <section class="report-section">
        <h2>Generate Reports</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-3 d-flex align-items-stretch">
                <div class="report-card w-100">
                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    <div class="card-title">Events Report</div>
                    <div class="card-desc">Generate a report of all events in the system.</div>
                    <a class="btn btn-danger mb-2" href="DownloadReport.php?type=events&format=pdf" target="_blank">Download PDF</a>
                    <a class="btn btn-secondary" href="DownloadReport.php?type=events&format=txt" target="_blank">Download TXT</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-stretch">
                <div class="report-card w-100">
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                    <div class="card-title">Promotions Report</div>
                    <div class="card-desc">Generate a report of all promotions.</div>
                    <a class="btn btn-danger mb-2" href="DownloadReport.php?type=promotions&format=pdf" target="_blank">Download PDF</a>
                    <a class="btn btn-secondary" href="DownloadReport.php?type=promotions&format=txt" target="_blank">Download TXT</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-stretch">
                <div class="report-card w-100">
                    <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="card-title">Attendance Report</div>
                    <div class="card-desc">Generate a report of all attendance records.</div>
                    <a class="btn btn-danger mb-2" href="DownloadReport.php?type=attendance&format=pdf" target="_blank">Download PDF</a>
                    <a class="btn btn-secondary" href="DownloadReport.php?type=attendance&format=txt" target="_blank">Download TXT</a>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-stretch">
                <div class="report-card w-100">
                    <div class="icon"><i class="fas fa-bullhorn"></i></div>
                    <div class="card-title">Announcements Report</div>
                    <div class="card-desc">Generate a report of all announcements.</div>
                    <a class="btn btn-danger mb-2" href="DownloadReport.php?type=announcements&format=pdf" target="_blank">Download PDF</a>
                    <a class="btn btn-secondary" href="DownloadReport.php?type=announcements&format=txt" target="_blank">Download TXT</a>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>