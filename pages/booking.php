<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../index.php");
    exit();
}

$adventure_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


// Get adventure details

$stmt = $conn->prepare("
    SELECT
        a.adventure_id,
        a.adventure_name,
        a.price,
        a.capacity,
        a.duration,
        l.location_name,
        l.district,
        ai.image_url
    FROM adventures a

    INNER JOIN locations l
        ON a.location_id = l.location_id

    LEFT JOIN adventure_images ai
        ON a.adventure_id = ai.adventure_id
        AND ai.is_main = TRUE

    WHERE a.adventure_id = ?
");

$stmt->bind_param("i", $adventure_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: ../index.php");
    exit();
}

$adventure = $result->fetch_assoc();

$stmt->close();


// Calculate already booked participants

$capacity_stmt = $conn->prepare("
    SELECT COALESCE(SUM(participants), 0) AS booked
    FROM bookings
    WHERE adventure_id = ?
    AND status IN ('PENDING', 'CONFIRMED')
");

$capacity_stmt->bind_param("i", $adventure_id);
$capacity_stmt->execute();

$capacity_result = $capacity_stmt->get_result();
$capacity_data = $capacity_result->fetch_assoc();

$booked = (int) $capacity_data["booked"];

$available = $adventure["capacity"] - $booked;

$capacity_stmt->close();


// Handle booking

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $participants = (int) $_POST["participants"];

    if ($participants <= 0) {

        $message = "Please select at least one participant.";
        $message_type = "error";

    } elseif ($participants > $available) {

        $message = "Only " . $available . " places are available.";
        $message_type = "error";

    } else {

        $total_amount =
            $participants * $adventure["price"];

        $insert = $conn->prepare("
            INSERT INTO bookings
            (
                user_id,
                adventure_id,
                booking_date,
                participants,
                total_amount,
                status
            )
            VALUES (?, ?, NOW(), ?, ?, 'PENDING')
        ");

        $insert->bind_param(
            "iiid",
            $user_id,
            $adventure_id,
            $participants,
            $total_amount
        );

        if ($insert->execute()) {

            $booking_id = $insert->insert_id;

            header(
                "Location: booking-success.php?id=" . $booking_id
            );

            exit();

        } else {

            $message = "Booking failed. Please try again.";
            $message_type = "error";
        }

        $insert->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Book <?php echo htmlspecialchars(
            $adventure["adventure_name"]
        ); ?>
        | ExploreX
    </title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .booking-page {
            padding: 130px 7% 80px;
        }

        .booking-container {
            max-width: 900px;
            margin: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .booking-card {
            padding: 30px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 25px;
            backdrop-filter: blur(20px);
        }

        .booking-image {
            height: 280px;
            border-radius: 20px;
            margin-bottom: 25px;

            background:
                linear-gradient(
                    to top,
                    rgba(0,0,0,.75),
                    transparent
                ),
                url("../assets/images/<?php
                    echo htmlspecialchars(
                        $adventure["image_url"]
                    );
                ?>") center/cover no-repeat;
        }

        .booking-card h1 {
            font-size: 35px;
            line-height: 1.1;
            margin-bottom: 12px;
        }

        .booking-location {
            color: #9da49a;
            margin-bottom: 20px;
        }

        .booking-price {
            color: #b5c889;
            font-size: 25px;
            font-weight: bold;
        }

        .available {
            margin-top: 10px;
            color: #9da49a;
        }

        .form-title {
            font-size: 28px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #dce1d8;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,.12);
            background: rgba(255,255,255,.06);
            color: white;
            outline: none;
        }

        .total-box {
            padding: 18px;
            margin: 20px 0;
            border-radius: 15px;
            background: rgba(139,155,98,.12);
            border: 1px solid rgba(181,200,137,.2);
        }

        .total-box span {
            color: #9da49a;
        }

        .total-amount {
            display: block;
            margin-top: 5px;
            font-size: 26px;
            color: #b5c889;
            font-weight: bold;
        }

        .confirm-button {
            width: 100%;
            border: none;
            padding: 16px;
            border-radius: 30px;
            background: #8b9b62;
            color: #10120d;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .error-message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 10px;
            background: rgba(180,50,50,.15);
            color: #ff9b9b;
        }

        @media (max-width: 800px) {

            .booking-container {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<nav class="navbar">

    <a href="../index.php" class="logo">
        Explore<span>X</span>
    </a>

    <div class="nav-links">

        <a href="../index.php">
            Home
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<main class="booking-page">

    <div class="booking-container">


        <!-- Adventure Information -->

        <div class="booking-card">

            <div class="booking-image"></div>

            <h1>
                <?php
                echo htmlspecialchars(
                    $adventure["adventure_name"]
                );
                ?>
            </h1>

            <p class="booking-location">

                📍

                <?php
                echo htmlspecialchars(
                    $adventure["location_name"]
                );
                ?>,

                <?php
                echo htmlspecialchars(
                    $adventure["district"]
                );
                ?>

            </p>

            <p class="booking-price">

                Rs.
                <?php
                echo number_format(
                    $adventure["price"],
                    2
                );
                ?>

                / person

            </p>

            <p class="available">

                👥
                <?php echo $available; ?>
                places available

            </p>

        </div>


        <!-- Booking Form -->

        <div class="booking-card">

            <h2 class="form-title">
                Book Your Adventure
            </h2>


            <?php if (!empty($message)): ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Number of Participants
                    </label>

                    <input
                        type="number"
                        name="participants"
                        id="participants"
                        min="1"
                        max="<?php echo $available; ?>"
                        value="1"
                        required
                    >

                </div>


                <div class="total-box">

                    <span>
                        Total Amount
                    </span>

                    <strong
                        class="total-amount"
                        id="totalAmount"
                    >
                        Rs.
                        <?php
                        echo number_format(
                            $adventure["price"],
                            2
                        );
                        ?>
                    </strong>

                </div>


                <button
                    type="submit"
                    class="confirm-button"
                >
                    Confirm Booking →
                </button>

            </form>

        </div>

    </div>

</main>


<script>

const participants =
    document.getElementById("participants");

const totalAmount =
    document.getElementById("totalAmount");

const price =
    <?php echo $adventure["price"]; ?>;


function updateTotal() {

    let quantity =
        parseInt(participants.value) || 1;

    let total =
        quantity * price;

    totalAmount.textContent =
        "Rs. " +
        total.toLocaleString(
            "en-LK",
            {
                minimumFractionDigits: 2
            }
        );
}


participants.addEventListener(
    "input",
    updateTotal
);

</script>

</body>

</html>