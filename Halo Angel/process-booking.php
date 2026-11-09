<?php
function parse_booking_date(string $value): ?DateTimeImmutable
{
    $trimmed = trim($value);
    if ($trimmed === "") {
        return null;
    }

    $candidates = [
        "M j, Y",
        "M d, Y",
        "F j, Y",
        "F d, Y",
    ];

    foreach ($candidates as $candidate) {
        $date = DateTimeImmutable::createFromFormat("!" . $candidate, $trimmed);
        if ($date !== false && $date->format($candidate) === $trimmed) {
            return $date;
        }
    }

    return null;
}

$bookingSaved = false;
$errorMessage = "";
$equipment = "";
$pickupDate = "";
$returnDate = "";
$totalDays = 0;
$totalAmount = 0.0;
$downpaymentAmount = 0.0;
$remainingBalance = 0.0;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: booking.html");
    exit();
}

$equipment = trim($_POST["equipment"] ?? "");
$pickupInput = trim($_POST["pickup_date"] ?? "");
$returnInput = trim($_POST["return_date"] ?? "");
$email = trim($_POST["email"] ?? "");
$contactName = trim($_POST["contact_name"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$termsAccepted = ($_POST["terms_acknowledged"] ?? "") === "1";

$rates = [
    "Sony ZV-E10" => [549, 499, 449],
    "DJI Osmo Pocket 3 Creator Combo" => [549, 499, 449],
    "DJI Osmo Action 4" => [499, 449, 399],
    "Insta360 X5" => [549, 499, 449],
    "Fujifilm Instax Mini Evo" => [299, 249],
    "Fujifilm Instax SQ6" => [299, 249],
];

$pickup = parse_booking_date($pickupInput);
$return = parse_booking_date($returnInput);
$today = new DateTimeImmutable("today");

if ($contactName === "" || strlen($contactName) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($email) > 100 || $mobile === "" || strlen($mobile) > 20) {
    $errorMessage = "Enter a valid contact name, email address, and mobile number.";
} elseif (!isset($rates[$equipment])) {
    $errorMessage = "Choose a valid camera from the booking list.";
} elseif ($pickup === null || $return === null || $return < $pickup || $pickup < $today) {
    $errorMessage = "Choose valid pickup and return dates. Pickup must be today or later, and return cannot be before pickup.";
} elseif (!$termsAccepted) {
    $errorMessage = "Please confirm that you understand this is a rental request.";
} else {
    $pickupDate = $pickup->format("Y-m-d");
    $returnDate = $return->format("Y-m-d");
    $totalDays = (int) $pickup->diff($return)->days + 1;

    $equipmentRates = $rates[$equipment];
    if ($totalDays >= 7 && count($equipmentRates) === 3) {
        $dailyRate = $equipmentRates[2];
    } elseif ($totalDays >= 4) {
        $dailyRate = $equipmentRates[1];
    } else {
        $dailyRate = $equipmentRates[0];
    }

    $totalAmount = $dailyRate * $totalDays;
    $downpaymentAmount = round($totalAmount / 2, 2);
    $remainingBalance = round($totalAmount - $downpaymentAmount, 2);

    try {
        require_once __DIR__ . "/db.php";

        $renterStmt = $conn->prepare("SELECT renter_id FROM renters WHERE email = ?");
        $renterStmt->bind_param("s", $email);
        $renterStmt->execute();
        $renterStmt->bind_result($renterId);
        $hasRenter = $renterStmt->fetch();
        $renterStmt->close();

        if (!$hasRenter) {
            $errorMessage = "No renter account was found for that email. Register first, or use the email on your renter account.";
        } else {
            $bookingStmt = $conn->prepare(
                "INSERT INTO bookings
                    (user_id, equipment_name, start_date, end_date, total_days, total_amount, downpayment_amount, remaining_balance)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $bookingStmt->bind_param(
                "isssiddd",
                $renterId,
                $equipment,
                $pickupDate,
                $returnDate,
                $totalDays,
                $totalAmount,
                $downpaymentAmount,
                $remainingBalance
            );
            $bookingStmt->execute();
            $bookingStmt->close();
            $bookingSaved = true;
        }
    } catch (mysqli_sql_exception $exception) {
        error_log("Booking database insert failed: " . $exception->getMessage());
        $errorMessage = "The booking request could not be saved. Check that the renters and bookings tables match the provided schema.";
    }
}
?>
<!doctype html>
<html lang="en" class="rental-page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Request - Halo Angel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="rental-page">
    <main class="container">
        <div class="breadcrumb"><a href="home.html">Home</a> / <a href="booking.html">Booking</a> / Request status</div>
        <div class="sub-heading"><?php echo $bookingSaved ? "REQUEST RECEIVED" : "REQUEST NOT COMPLETED"; ?></div>
        <h1 class="page-title"><?php echo $bookingSaved ? "Booking request saved." : "We couldn't save that request."; ?></h1>
        <?php if ($bookingSaved): ?>
            <p class="subtitle">
                Your request is pending review. The booking does not confirm availability or payment.
            </p>
            <div class="card">
                <div class="card-content">
                    <p><strong>Pickup:</strong> <?php echo htmlspecialchars($pickupDate, ENT_QUOTES, "UTF-8"); ?></p>
                    <p><strong>Return:</strong> <?php echo htmlspecialchars($returnDate, ENT_QUOTES, "UTF-8"); ?></p>
                    <p><strong>Equipment:</strong> <?php echo htmlspecialchars($equipment, ENT_QUOTES, "UTF-8"); ?></p>
                    <p><strong>Duration:</strong> <?php echo $totalDays; ?> day(s)</p>
                    <p><strong>Estimated total:</strong> ₱<?php echo number_format($totalAmount, 2); ?></p>
                    <p><strong>Downpayment:</strong> ₱<?php echo number_format($downpaymentAmount, 2); ?></p>
                    <p><strong>Remaining balance:</strong> ₱<?php echo number_format($remainingBalance, 2); ?></p>
                </div>
            </div>
        <?php else: ?>
            <p role="alert" class="subtitle" style="color: #b91c1c;">
                <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, "UTF-8"); ?>
            </p>
        <?php endif; ?>
        <p><a class="btn-quote inline-btn" href="booking.html">Return to booking</a></p>
    </main>
</body>
</html>
