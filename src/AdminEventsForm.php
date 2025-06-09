<?php
session_start();
require_once 'DBconnection.php';

// Check if the user is logged in and is admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: Login.php");
    exit;
}

// Table creation code removed since table already exists

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = trim($_POST['location']);

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO afpro_events (title, description, start_date, end_date, location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $description, $start_date, $end_date, $location);

    if ($stmt->execute()) {
        $success = "Event created successfully.";
    } else {
        $error = "Error creating event: " . $conn->error;
    }
    $stmt->close();
}
?>

<section class="p-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div id="alert-container"></div>
            <div class="card shadow">
                <div class="card-body">
                    <h3 class="text-center mb-4">Create New Event</h3>
                    <form id="event-create-form" method="POST" action="AdminCreateEvent.php" novalidate>
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Event Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Event Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Event End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Event Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-2"></i>Create Event
                            </button>
                            <a href="#" onclick="loadPage('AdminViewEvent.php', 'events-submenu')" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.getElementById('event-create-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = e.target;
    var formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        var alertContainer = document.getElementById('alert-container');
        if (data.success) {
            alertContainer.innerHTML = `<div class='alert alert-success text-center' role='alert'>${data.message}</div>`;
            form.reset();
        } else {
            alertContainer.innerHTML = `<div class='alert alert-danger text-center' role='alert'>${data.message}</div>`;
        }
    })
    .catch(error => {
        document.getElementById('alert-container').innerHTML = `<div class='alert alert-danger text-center' role='alert'>Error: ${error.message}</div>`;
    });
});
</script>
<?php $conn->close(); ?>