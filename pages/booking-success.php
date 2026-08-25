<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$booking_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT
        b.booking_id,
        b.booking_date,
        b.participants,
        b.total_amount,
        b.status,
        a.adventure_name,
        l.location_name
    FROM bookings b

    INNER JOIN adventures a
        ON b.adventure_id = a.adventure_id

    INNER JOIN locations l
        ON a.location_id = l.location_id

    WHERE b.booking_id = ?
    AND b.user_id = ?
");

$stmt->bind_param(
    "ii",
    $booking_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Booking Confirmed | ExploreX
    </title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .success-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
        }

        .success-card {
            width: 100%;
            max-width: 600px;
            padding: 45px;
            text-align: center;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 30px;
            backdrop-filter: blur(20px);
        }

        .success-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .success-card h1 {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .success-card > p {
            color: #9da49a;
            margin-bottom: 30px;
        }

        .booking-summary {
            text-align: left;
            padding: 20px;
            border-radius: 18px;
            background: rgba(255,255,255,.04);
            margin-bottom: 25px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row span {
            color: #9da49a;
        }

        .success-button {
            display: block;
            padding: 15px;
            border-radius: 30px;
            background: #8b9b62;
            color: #10120d;
            font-weight: bold;
        }

    </style>

</head>

<body>

<main class="success-page">

    <div class="success-card">

        <div class="success-icon">
            ✓
        </div>

        <h1>
            Booking Successful!
        </h1>

        <p>
            Your adventure booking has been created
            successfully.
        </p>


        <div class="booking-summary">

            <div class="summary-row">

                <span>
                    Booking ID
                </span>

                <strong>
                    #<?php echo $booking["booking_id"]; ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Adventure
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking["adventure_name"]
                    );
                    ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Location
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking["location_name"]
                    );
                    ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Participants
                </span>

                <strong>
                    <?php echo $booking["participants"]; ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Total
                </span>

                <strong>
                    Rs.
                    <?php
                    echo number_format(
                        $booking["total_amount"],
                        2
                    );
                    ?>
                </strong>

            </div>


            <div class="summary-row">

                <span>
                    Status
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $booking["status"]
                    );
                    ?>
                </strong>

            </div>

        </div>


        <a
            href="dashboard.php"
            class="success-button"
        >
            View My Bookings →
        </a>

    </div>

</main>

</body>

</html>