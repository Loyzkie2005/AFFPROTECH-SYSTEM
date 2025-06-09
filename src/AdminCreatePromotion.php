<?php
session_start();
header('Content-Type: application/json');
require_once 'DBconnection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['image'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = trim($_POST['location']);
    $created_by = $_SESSION['user_id'] ?? null;
    
    // Server-side validation
    if (!$title) {
        echo json_encode(['success' => false, 'message' => 'Title is required.']);
        exit;
    }
    if (!$description) {
        echo json_encode(['success' => false, 'message' => 'Description is required.']);
        exit;
    }
    if ($price === '' || !is_numeric($price) || $price < 0) {
        echo json_encode(['success' => false, 'message' => 'Valid price is required.']);
        exit;
    }
    if (!$start_date) {
        echo json_encode(['success' => false, 'message' => 'Start date is required.']);
        exit;
    }
    if (!$end_date) {
        echo json_encode(['success' => false, 'message' => 'End date is required.']);
        exit;
    }
    if (strtotime($end_date) <= strtotime($start_date)) {
        echo json_encode(['success' => false, 'message' => 'End date must be after start date.']);
        exit;
    }
    if (!$location) {
        echo json_encode(['success' => false, 'message' => 'Location is required.']);
        exit;
    }
    if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        echo json_encode(['success' => false, 'message' => 'Image is required.']);
        exit;
    }
    if (!$created_by) {
        echo json_encode(['success' => false, 'message' => 'User ID not found in session. Please log in again.']);
        exit;
    }

    // Handle image upload
    $image_url = '';
    if ($_FILES['image']['error'] == 0) {
        $target_dir = "img/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $image_name = uniqid() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO afpro_promotion (title, description, price, start_date, end_date, location, image_url, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssssi", $title, $description, $price, $start_date, $end_date, $location, $image_url, $created_by);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Promotion created successfully!']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating promotion: ' . $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}
echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>