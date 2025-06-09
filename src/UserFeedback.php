<?php
session_start();
require_once 'DBconnection.php';

// Check if the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}

// Get user_id and event_id
$user_id = $_SESSION['user_id'] ?? '';
$event_id = $_GET['event_id'] ?? '';
?>
<section class="p-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="text-center mb-5">
                    <h2 class="fs-4 fw-semibold mt-2">About Your Feedback</h2>
                </div>
                <div class="bg-white p-5 shadow-sm rounded">
                    <div class="mb-4">
                        <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 mb-3" onclick="loadPage('UserFeedbackList.php');">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </button>
                    </div>
                    <?php if (empty($user_id) || empty($event_id)): ?>
                        <div class="alert alert-danger">User or Event information is missing. Please try again or contact support.</div>
                    <?php endif; ?>
                    <form id="feedback-form" method="POST" action="javascript:void(0);">
                        <div id="feedback-alert-container"></div>
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event_id); ?>">
                        <div class="mb-3">
                            <label for="message" class="form-label">About Your Feedback <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                        </div>
                        <div class="mb-4 d-flex align-items-center">
                            <label for="rating" class="form-label me-3">Rating <span class="text-danger">*</span></label>
                            <div class="rating">
                                <input type="radio" hidden name="rating" id="rating-opt5" value="5">
                                <label for="rating-opt5"><span></span></label>
                                <input type="radio" hidden name="rating" id="rating-opt4" value="4">
                                <label for="rating-opt4"><span></span></label>
                                <input type="radio" hidden name="rating" id="rating-opt3" value="3">
                                <label for="rating-opt3"><span></span></label>
                                <input type="radio" hidden name="rating" id="rating-opt2" value="2">
                                <label for="rating-opt2"><span></span></label>
                                <input type="radio" hidden name="rating" id="rating-opt1" value="1">
                                <label for="rating-opt1"><span></span></label>
                            </div>
                        </div>
                        <div id="rating-validation-message" class="text-danger mb-3" style="display:none; font-size:0.95em;"></div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" <?php if (empty($user_id) || empty($event_id)) echo 'disabled'; ?>>Submit Feedback</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
body { background: #e2e1d9; min-height: 100vh; }
.rating { display: flex; flex-direction: row-reverse; font-size: 0; gap: 0.2em; padding: 0; color: gray; }
.rating input[type='radio'] { display: none; }
label[for^='rating-opt'] { float: none; padding: 0; font-size: 2.2rem; cursor: pointer; margin: 0 0.1em; position: relative; transition: transform 0.2s; }
label[for^='rating-opt']::before { content: "\2606"; display: inline-block; transition: 0.2s; }
input[name='rating']:checked ~ label[for^='rating-opt']::before,
label[for^='rating-opt']:hover ~ label[for^='rating-opt']::before,
label[for^='rating-opt']:hover::before { content: "\2605"; color: orange; filter: drop-shadow(0 0 4px); transform: scale(1.15) rotate(.2turn); }
label[for^='rating-opt'] span { opacity: 0; position: absolute; left: 50%; bottom: -2em; width: max-content; text-align: center; font-size: 1rem; white-space: nowrap; transition: 0.15s ease-out; pointer-events: none; transform: translateX(-50%); color: #333; }
label[for^='rating-opt']:hover span { opacity: 1; transform: translateX(-50%) scale(1.1); }
#feedback-alert-container .alert { margin-bottom: 1rem; border-radius: 0.25rem; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('feedback-form');
    const alertContainer = document.getElementById('feedback-alert-container');
    const ratingValidation = document.getElementById('rating-validation-message');
    if (form && !form.dataset.listenerAttached) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            const ratingChecked = form.querySelector('input[name="rating"]:checked');
            const message = form.querySelector('textarea[name="message"]').value.trim();
            let hasError = false;
            if (!message) {
                alertContainer.innerHTML = '<div class="alert alert-danger">Please fill in all required fields.</div>';
                setTimeout(() => { alertContainer.innerHTML = ''; }, 2500);
                hasError = true;
            }
            if (!ratingChecked) {
                ratingValidation.textContent = 'Rating is required.';
                ratingValidation.style.display = 'block';
                setTimeout(() => { ratingValidation.style.display = 'none'; }, 2500);
                hasError = true;
            } else {
                ratingValidation.style.display = 'none';
            }
            if (hasError) return;
            fetch('UserFeedbackSubmit.php', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Network error: ${response.status} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alertContainer.innerHTML = '<div class="alert alert-success" style="border-left: 5px solid #000080; background-color: #e6f4ea;">' + data.message + '</div>';
                    form.reset();
                } else {
                    alertContainer.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                }
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                setTimeout(() => { alertContainer.innerHTML = ''; }, 2500);
            })
            .catch(error => {
                alertContainer.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                setTimeout(() => { alertContainer.innerHTML = ''; }, 2500);
                console.error('Fetch error:', error);
            });
        });
        form.dataset.listenerAttached = 'true'; // Prevent duplicate listeners
    }
});
</script> 