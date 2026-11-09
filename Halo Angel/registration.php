<?php
$isSubmitted = false;
$errorMessage = "";
$firstName = $lastName = $email = $phoneNumber = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $firstName = trim($_POST["first_name"] ?? "");
  $lastName = trim($_POST["last_name"] ?? "");
  $email = trim($_POST["email"] ?? "");
  $phoneNumber = trim($_POST["phone_number"] ?? "");
  $password = $_POST["password"] ?? "";
  $termsAgreed = ($_POST["terms"] ?? "") === "Agreed";

  if ($firstName === "" || strlen($firstName) > 50 || $lastName === "" || strlen($lastName) > 50
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100
    || $phoneNumber === "" || strlen($phoneNumber) > 20) {
    $errorMessage = "Enter a valid first name, last name, email address, and phone number.";
  } elseif (strlen($password) < 8 || !preg_match("/[0-9]/", $password)) {
    $errorMessage = "Password must be at least 8 characters and include a number.";
  } elseif (!$termsAgreed) {
    $errorMessage = "You must agree to the Terms & Conditions to register.";
  } else {
    try {
      require_once __DIR__ . "/db.php";
      $passwordHash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare(
        "INSERT INTO renters (first_name, last_name, email, password_hash, phone_number)
         VALUES (?, ?, ?, ?, ?)"
      );
      $stmt->bind_param("sssss", $firstName, $lastName, $email, $passwordHash, $phoneNumber);
      $stmt->execute();
      $stmt->close();
      $isSubmitted = true;
    } catch (mysqli_sql_exception $exception) {
      if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
      }
      if ($exception->getCode() === 1062) {
        $errorMessage = "An account with that email address already exists.";
      } else {
        error_log("Registration database insert failed: " . $exception->getMessage());
        $errorMessage = "Registration could not be completed. Check the renters table columns and try again.";
      }
    }
  }
}
?>
<!doctype html>
<html lang="en" class="registration-page">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Halo Angel</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body class="registration-page">
    <div>
      <header class="site-header">
        <div class="header-container">
          <a href="home.html" class="logo-group" aria-label="Halo Angel home">
            <img
              src="image/OfficialLogo.png"
              class="site-logo"
              alt="Halo Angel Camera Rental"
            />
          </a>

          <nav class="nav-links">
            <a href="catalog.html" class="nav-link">Explore gear</a>
            <a href="about_us.html#how-it-works" class="nav-link">How it works</a>
            <a href="about_us.html#about" class="nav-link">Why Halo Angel</a>
            <a href="customer_guidelines.html#support" class="nav-link">Support</a>
          </nav>

          <div class="header-controls">
            <a class="icon-btn" href="catalog.html#searchInput" aria-label="Search cameras">
              <svg
                width="18"
                height="18"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <circle cx="11" cy="11" r="8" stroke-width="1.8" />
                <path
                  d="M21 21l-4.35-4.35"
                  stroke-width="1.8"
                  stroke-linecap="round"
                />
              </svg>
            </a>
            <a href="login.html" class="nav-link">Log in</a>
            <a href="booking.html#bookingMainForm" class="cart-group rental-bag-link" aria-label="Rental bag and booking">
              <img class="rental-bag-icon" src="image/shopping-bag.png" alt="" />
              <span>Rental bag</span>
            </a>
          </div>
        </div>
      </header>

      <main class="main-content">
        <div class="content-card">
          <div class="left-panel">
            <div>
              <div class="orange-tag">
                <span class="orange-dot"></span>
                <span>BIG IDEAS. BETTER GEAR.</span>
              </div>
              <h1 class="hero-heading">More room for<br />what you create.</h1>
              <p class="hero-subtext">
                Great gear, without the big purchase. Create an account and make
                your next idea happen.
              </p>

              <div class="product-image-container">
                <img
                  src="image/SONY ZVE10.png"
                  class="product-image"
                  alt="Sony ZV-E10 camera"
                />
              </div>

              <div class="product-info-card">
                <div>
                  <div class="product-title">Sony Zv-e10</div>
                  <div class="product-subtitle">
                    A little inspiration for your next shoot.
                  </div>
                </div>
                <div class="product-price">P339/ day ↗</div>
              </div>
            </div>

            <div class="badge-feature">
              <div class="check-icon-wrapper">
                <svg
                  width="10"
                  height="10"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2.5"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
              <div>
                <div class="feature-title">Shoot-ready. Every time.</div>
                <div class="feature-desc">
                  Professionally cleaned &amp; tested. Ready for your idea.
                </div>
              </div>
            </div>
          </div>

          <div class="right-panel">
            <div class="form-wrapper">
              <div class="orange-tag">MAKE SOMETHING WORTH FRAMING.</div>
              <h2 class="form-heading">Create your account.</h2>
              <p class="form-subtext">
                Your next great setup is a few details away.
              </p>

              <?php if ($errorMessage !== ""): ?>
                <p role="alert" class="form-subtext" style="color: #b91c1c;">
                  <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8"); ?>
                </p>
              <?php elseif ($isSubmitted): ?>
                <p role="status" class="form-subtext" style="color: #166534;">
                  Your account was created and saved to the renters table.
                </p>
              <?php endif; ?>

              <form class="register-form" action="registration.php" method="post">
                <div class="form-row">
                  <div class="form-group">
                    <label class="form-label">First name</label>
                    <input
                      type="text"
                      name="first_name"
                      placeholder="First name"
                      class="form-input"
                      maxlength="50"
                      value="<?php echo htmlspecialchars($firstName, ENT_QUOTES, "UTF-8"); ?>"
                      required
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Last name</label>
                    <input
                      type="text"
                      name="last_name"
                      placeholder="Last name"
                      class="form-input"
                      maxlength="50"
                      value="<?php echo htmlspecialchars($lastName, ENT_QUOTES, "UTF-8"); ?>"
                      required
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label">Email address</label>
                  <input
                    type="email"
                    name="email"
                    placeholder="you@example.com"
                    class="form-input"
                    maxlength="100"
                    value="<?php echo htmlspecialchars($email, ENT_QUOTES, "UTF-8"); ?>"
                    required
                  />
                </div>

                <div class="form-group">
                  <label class="form-label">Phone number</label>
                  <input
                    type="tel"
                    name="phone_number"
                    placeholder="Phone number"
                    class="form-input"
                    maxlength="20"
                    value="<?php echo htmlspecialchars($phoneNumber, ENT_QUOTES, "UTF-8"); ?>"
                    required
                  />
                </div>

                <div class="form-group">
                  <label class="form-label">Password</label>
                  <div class="password-input-wrapper">
                    <input
                      type="password"
                      name="password"
                      placeholder="Create a password"
                      class="form-input"
                      minlength="8"
                      pattern="(?=.*[0-9]).{8,}"
                      required
                    />
                    <button
                      type="button"
                      class="password-toggle-btn"
                      aria-label="Toggle password visibility"
                    >
                      <svg
                        width="16"
                        height="16"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="1.5"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                        />
                      </svg>
                    </button>
                  </div>
                  <div class="password-hint">
                    Use at least 8 characters, including a number.
                  </div>
                </div>

                <div class="checkbox-group">
                  <input
                    id="terms"
                    type="checkbox"
                    name="terms"
                    value="Agreed"
                    class="checkbox-input"
                    required
                  />
                  <label for="terms" class="checkbox-label">
                    I agree to the
                    <a
                      href="#termsModal"
                      class="link-underline"
                      data-bs-toggle="modal"
                      aria-controls="termsModal"
                      >Terms &amp; Conditions</a
                    >
                    and
                    <a
                      href="#privacyModal"
                      class="link-underline"
                      data-bs-toggle="modal"
                      aria-controls="privacyModal"
                      >Privacy policy</a
                    >.
                  </label>
                </div>

                <button type="submit" class="submit-btn">
                  Create account ↗
                </button>

                <div class="login-prompt">
                  Already have an account?
                  <a href="login.html" class="login-link">Log in ↗</a>
                </div>

                <div class="form-footer-note">
                  Free to join. No commitment until you rent.
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>
    </div>

    <footer class="site-footer">
      <div class="footer-container">
        <div>© 2026 FrameRent. Made for the ones who make.</div>
        <div class="footer-links">
          <a
            href="#privacyModal"
            class="footer-link"
            data-bs-toggle="modal"
            aria-controls="privacyModal"
            >Privacy policy</a
          >
          <a
            href="#termsModal"
            class="footer-link"
            data-bs-toggle="modal"
            aria-controls="termsModal"
            >Rental terms</a
          >
          <a href="about_us.html#contact" class="footer-link">Need a hand? Contact us ↗</a>
        </div>
      </div>
    </footer>

    <div
      class="modal fade"
      id="termsModal"
      tabindex="-1"
      aria-labelledby="termsModalLabel"
      aria-hidden="true"
    >
      <div
        class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title fs-5" id="termsModalLabel">
              Terms &amp; Conditions
            </h2>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <p>
              <strong>Welcome to Halo Angel Camera Rental!</strong> By creating
              an account or renting equipment through our platform, you agree to
              the following terms and conditions:
            </p>

            <ol>
              <li>
                <strong>Eligibility &amp; Account Responsibility</strong>
                <ul>
                  <li>
                    You must provide accurate and complete personal details when
                    creating an account.
                  </li>
                  <li>
                    You are responsible for maintaining the confidentiality of
                    your account password and activity.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Rental Agreements &amp; Daily Rates</strong>
                <ul>
                  <li>
                    Rental prices (e.g., Sony ZV-E10 at ₱339/day) are billed on
                    a daily basis as specified during checkout.
                  </li>
                  <li>
                    Rental periods begin and end on the dates agreed upon during
                    booking.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Equipment Care &amp; Inspection</strong>
                <ul>
                  <li>
                    All gear is professionally cleaned, tested, and handed over
                    in working condition.
                  </li>
                  <li>
                    You are required to inspect the equipment upon receipt and
                    report any existing defects immediately.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Loss, Damage, &amp; Late Returns</strong>
                <ul>
                  <li>
                    <strong>Damage or Loss:</strong> Renter is financially
                    responsible for any loss, theft, or physical damage
                    occurring to the equipment during the rental period.
                  </li>
                  <li>
                    <strong>Late Fees:</strong> Equipment returned past the
                    agreed schedule will incur additional daily rental fees or
                    overdue charges.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Proper Use</strong>
                <ul>
                  <li>
                    Equipment must be used solely for lawful creative or
                    professional purposes and handled with reasonable care.
                  </li>
                </ul>
              </li>
            </ol>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      class="modal fade"
      id="privacyModal"
      tabindex="-1"
      aria-labelledby="privacyModalLabel"
      aria-hidden="true"
    >
      <div
        class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h2 class="modal-title fs-5" id="privacyModalLabel">
              Privacy Policy
            </h2>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body">
            <p><strong>Effective Date:</strong> October 30, 2026</p>

            <p>
              At <strong>Halo Angel Camera Rental</strong>, we respect your
              privacy and are committed to protecting your personal information.
            </p>

            <ol>
              <li>
                <strong>Information We Collect</strong>
                <ul>
                  <li>
                    <strong>Personal Account Data:</strong> Full name, email
                    address, password, and contact details provided during
                    registration.
                  </li>
                  <li>
                    <strong>Transaction Data:</strong> Rental history, payment
                    status, and gear booking preferences.
                  </li>
                </ul>
              </li>

              <li>
                <strong>How We Use Your Data</strong>
                <ul>
                  <li>To create, verify, and manage your user account.</li>
                  <li>
                    To process camera rental bookings, scheduling, and returns.
                  </li>
                  <li>
                    To communicate important updates regarding your reservations
                    or support inquiries.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Data Protection &amp; Storage</strong>
                <ul>
                  <li>
                    Your password and personal data are encrypted and securely
                    stored.
                  </li>
                  <li>
                    We do not sell, rent, or trade your personal information to
                    third parties.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Your Rights</strong>
                <ul>
                  <li>
                    You have the right to access, update, or request the
                    deletion of your account details at any time by contacting
                    our support team.
                  </li>
                </ul>
              </li>

              <li>
                <strong>Contact Us</strong>
                <p>
                  If you have questions, concerns, or requests regarding this
                  Privacy Policy or your personal data, please contact us at:
                </p>
                <ul>
                  <li>Email: officialhaloangel@gmail.com</li>
                  <li>Phone: 098634906</li>
                </ul>
              </li>
            </ol>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      document
        .querySelector(".password-toggle-btn")
        .addEventListener("click", function () {
          const passwordInput = document.querySelector(
            'input[name="password"]',
          );
          passwordInput.type =
            passwordInput.type === "password" ? "text" : "password";
        });
    </script>
  </body>
</html>