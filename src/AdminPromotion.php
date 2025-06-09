<?php
session_start();
require_once 'DBconnection.php';

// Only allow admins
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("Location: Login.php");
    exit;
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<section class="p-4" style="overflow-y: auto; max-height: 90vh;">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div id="promotion-alert-container"></div>
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h3 class="text-center mb-4">
                        Create Promotion
                    </h3>
                    <form id="promotion-create-form" action="AdminCreatePromotion.php" method="POST" enctype="multipart/form-data" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                            <div class="invalid-feedback">Please provide a title.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide a description.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" name="price" class="form-control" required min="0">
                            </div>
                            <div class="invalid-feedback">Please provide a valid price.</div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                                <div class="invalid-feedback">Please provide a start date.</div>
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                                <div class="invalid-feedback">Please provide an end date.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" required>
                            <div class="invalid-feedback">Please provide a location.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Food Picture</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <div class="invalid-feedback">Please upload an image.</div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Create Promotion
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('promotion-create-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Get all required fields
            const title = form.querySelector('input[name="title"]').value.trim();
            const description = form.querySelector('textarea[name="description"]').value.trim();
            const price = form.querySelector('input[name="price"]').value.trim();
            const startDate = form.querySelector('input[name="start_date"]').value.trim();
            const endDate = form.querySelector('input[name="end_date"]').value.trim();
            const location = form.querySelector('input[name="location"]').value.trim();
            const image = form.querySelector('input[name="image"]').files[0];

            let errorMsg = '';
            if (!title) errorMsg = 'Title is required.';
            else if (!description) errorMsg = 'Description is required.';
            else if (!price || isNaN(price) || Number(price) < 0) errorMsg = 'Valid price is required.';
            else if (!startDate) errorMsg = 'Start date is required.';
            else if (!endDate) errorMsg = 'End date is required.';
            else if (!location) errorMsg = 'Location is required.';
            else if (!image) errorMsg = 'Image is required.';
            else if (new Date(endDate) <= new Date(startDate)) errorMsg = 'End date must be after start date.';

            if (errorMsg) {
                e.preventDefault();
                const alertContainer = document.getElementById('promotion-alert-container');
                alertContainer.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">${errorMsg}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                return false;
            }
        });
    }
});
</script>