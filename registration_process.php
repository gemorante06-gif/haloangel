<?php

$isSubmitted = false;
$firstName = $lastName = $email = $password = $maskedPassword = $terms = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = isset($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name'])) : '';
    $lastName  = isset($_POST['last_name'])  ? htmlspecialchars(trim($_POST['last_name']))  : '';
    $email     = isset($_POST['email'])      ? htmlspecialchars(trim($_POST['email']))      : '';
    $password  = isset($_POST['password'])   ? htmlspecialchars($_POST['password'])         : '';
    $terms     = isset($_POST['terms'])      ? htmlspecialchars($_POST['terms'])            : 'Not Agreed';

    $maskedPassword = strlen($password) > 0 ? str_repeat('•', strlen($password)) : 'None provided';

    $isSubmitted = true;
} else {
    header("Location: registration.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Process - Halo Angel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

    <style>
        .modal-content {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        .modal-header {
            background-color: #fcfbf9;
            border-bottom: 1px solid #f3f4f6;
            padding: 20px 24px;
        }
        .modal-title {
            font-size: 16px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .modal-body {
            padding: 24px;
            background-color: #ffffff;
        }
        .modal-footer {
            background-color: #fcfbf9;
            border-top: 1px solid #f3f4f6;
            padding: 16px 24px;
        }
        .modal-data-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .modal-data-row:last-child {
            border-bottom: none;
        }
        .modal-info-label {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .modal-info-value {
            font-size: 13px;
            color: #111827;
            font-weight: 500;
            text-align: right;
        }
        .modal-btn-action {
            background-color: #704bc5;
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.15s ease;
            text-decoration: none;
        }
        .modal-btn-action:hover {
            background-color: #5b3ca3;
            color: #ffffff;
        }
        .confirmation-center-box {
            max-width: 540px;
            margin: 60px auto;
            text-align: center;
            padding: 0 24px;
        }
    </style>
</head>
<body>
    <div>
        <header class="site-header">
            <div class="header-container">
                <a href="registration.html" class="logo-group" aria-label="Halo Angel home">
                    <img src="image/OfficialLogo.png" class="site-logo" alt="Halo Angel Camera Rental" />
                </a>

                <nav class="nav-links">
                    <a href="registration.html" class="nav-link">Back to registration</a>
                </nav>

                <div class="header-controls">
                    <a href="registration.html" class="nav-link">Register Again</a>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="confirmation-center-box">
                <div class="orange-tag" style="justify-content: center;">
                    <span class="orange-dot"></span>
                    <span>SUBMISSION RECEIVED</span>
                </div>
                <h1 class="form-heading" style="font-size: 32px;">Account Request Processed</h1>
                <p class="form-subtext" style="margin: 12px auto 24px auto;">
                    Your form data was successfully captured via PHP. View your submitted details in the popup modal below.
                </p>
                <button type="button" class="submit-btn" style="max-width: 240px; margin: 0 auto;" data-bs-toggle="modal" data-bs-target="#registrationModal">
                    Open Summary Modal ↗
                </button>
            </div>
        </main>
    </div>

    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationModalLabel">Submitted Registration Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($isSubmitted): ?>
                        <div class="modal-data-row">
                            <div class="modal-info-label">Full Name</div>
                            <div class="modal-info-value"><?php echo $firstName . ' ' . $lastName; ?></div>
                        </div>

                        <div class="modal-data-row">
                            <div class="modal-info-label">Email Address</div>
                            <div class="modal-info-value"><?php echo $email; ?></div>
                        </div>

                        <div class="modal-data-row">
                            <div class="modal-info-label">Password</div>
                            <div class="modal-info-value"><?php echo $maskedPassword; ?></div>
                        </div>

                        <div class="modal-data-row">
                            <div class="modal-info-label">Terms & Conditions</div>
                            <div class="modal-info-value">
                                <span style="display: inline-block; padding: 2px 8px; font-size: 11px; font-weight: 600; background: #f3e8ff; color: #6b21a8; border-radius: 4px;">
                                    <?php echo htmlspecialchars($terms); ?>
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="font-size: 13px; color: #6b7280; text-align: center;">No registration information found.</p>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <a href="registration.html" class="nav-link" style="font-size: 12px; font-weight: 600;">← Back to form</a>
                    <button type="button" class="modal-btn-action" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-container">
            <div>© 2026 FrameRent. Made for the ones who make.</div>
            <div class="footer-links">
                <a href="#" class="footer-link">Privacy policy</a>
                <a href="#" class="footer-link">Rental terms</a>
                <a href="#" class="footer-link">Need a hand? Contact us ↗</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var registrationModal = new bootstrap.Modal(document.getElementById('registrationModal'));
            registrationModal.show();
        });
    </script>
</body>
</html>