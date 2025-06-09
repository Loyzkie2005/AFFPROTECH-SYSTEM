<?php
session_start();
require_once 'DBconnection.php';

// Only allow logged-in users
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: Login.php");
    exit;
}

// Fetch promotions
$promotions = [];
$result = $conn->query("SELECT * FROM afpro_promotion ORDER BY start_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $promotions[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Promotions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .card.promo-card {
            width: 340px;
            height: 340px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.10);
            margin: 0 auto 24px auto;
            background: #fff;
        }
        .card-img-top.promo-img {
            width: 320px;
            height: 200px;
            object-fit: cover;
            border-radius: 16px;
            margin: 10px auto 0 auto;
        }
        .card-header {
            width: 100%;
            background: #000080 !important;
            color: #fff;
            text-align: center;
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
            padding: 0.5rem 0.5rem 0 0.5rem !important;
        }
        .card-title {
            font-size: 1.2rem !important;
            font-weight: bold;
            margin-bottom: 0;
        }
        .card-body {
            flex: 1 1 auto;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 0.5rem 1rem 0 1rem !important;
        }
        .card-footer {
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem 1rem 1rem !important;
            background: none;
            border: none;
        }
        @media (max-width: 900px) {
            .card { width: 100%; min-width: 0; min-height: 0; max-width: 100%; max-height: none; }
            .card-img-top { width: 100%; height: 180px; }
        }
    </style>
</head>
<body>
    <section class="p-4">
        <div class="container">
            <h2 class="text-center mb-4">All Promotions</h2>
            <div class="row g-4 justify-content-center align-items-stretch">
                <?php if (empty($promotions)): ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info">No promotions found.</div>
                    </div>
                <?php endif; ?>
                <?php foreach ($promotions as $promo): ?>
                    <div class="col-12 col-sm-6 col-md-4 d-flex align-items-stretch justify-content-center">
                        <div class="card mb-4 d-flex flex-column">
                            <div class="card-header bg-primary text-white text-center" style="padding: 1rem;">
                                <h5 class="card-title mb-0" style="font-size: 1.5rem; font-weight: bold;">
                                    <?php echo htmlspecialchars($promo['title']); ?>
                                </h5>
                            </div>
                            <img src="img/<?php echo htmlspecialchars(basename($promo['image_url'])); ?>"
                                 class="card-img-top promotion-zoom-img"
                                 alt="Promotion Image"
                                 style="height:220px;object-fit:cover;cursor:pointer;"
                                 data-title="<?= htmlspecialchars($promo['title']) ?>"
                                 data-description="<?= htmlspecialchars($promo['description']) ?>"
                                 data-price="<?= htmlspecialchars($promo['price']) ?>"
                                 data-location="<?= htmlspecialchars($promo['location']) ?>"
                            >
                            <div class="card-body text-center">
                                <div class="fs-4 fw-semibold mb-2">₱<?php echo number_format($promo['price'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Promotion Image Zoom Modal (copied from admin) -->
    <div class="modal fade" id="promotionImageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="promotionImageTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="promotionImageZoom" src="" alt="Promotion Image" class="img-fluid rounded mb-3" style="max-height: 400px; transition: transform 0.3s;">
                    <div id="promotionImageDetails" class="text-start"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.promotion-img, .promotion-zoom-img').forEach(img => {
        img.addEventListener('click', function() {
          document.getElementById('promotionImageZoom').src = this.src;
          document.getElementById('promotionImageTitle').textContent = this.getAttribute('data-title');
          // Format price with commas
          const price = Number(this.getAttribute('data-price')).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
          document.getElementById('promotionImageDetails').innerHTML = `
            <strong>Description:</strong> ${this.getAttribute('data-description')}<br>
            <strong>Price:</strong> ₱${price}<br>
            <strong>Location:</strong> ${this.getAttribute('data-location')}
          `;
          new bootstrap.Modal(document.getElementById('promotionImageModal')).show();
        });
      });
    });
    </script>
</body>
</html>
