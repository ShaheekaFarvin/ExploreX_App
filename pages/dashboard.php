<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "USER") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];


// Get user's bookings

$stmt = $conn->prepare("
    SELECT
        b.booking_id,
        b.booking_date,
        b.participants,
        b.total_amount,
        b.status,

        a.adventure_id,
        a.adventure_name,

        l.location_name,
        l.district,

        ai.image_url

    FROM bookings b

    INNER JOIN adventures a
        ON b.adventure_id = a.adventure_id

    INNER JOIN locations l
        ON a.location_id = l.location_id

    LEFT JOIN adventure_images ai
        ON a.adventure_id = ai.adventure_id
        AND ai.is_main = TRUE

    WHERE b.user_id = ?

    ORDER BY b.booking_date DESC
");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$bookings = $stmt->get_result();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        My Dashboard | ExploreX
    </title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .dashboard-page {
            padding: 130px 7% 80px;
        }

        .dashboard-header {
            margin-bottom: 45px;
        }

        .dashboard-header h1 {
            font-size: clamp(40px, 5vw, 65px);
            line-height: 1;
            margin-bottom: 12px;
        }

        .dashboard-header p {
            color: #9da49a;
        }

        .welcome-name {
            color: #b5c889;
        }

        .booking-grid {
            display: grid;
            grid-template-columns:
                repeat(3, 1fr);
            gap: 22px;
        }

        .booking-card {
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 25px;
            background: rgba(255,255,255,.05);
            backdrop-filter: blur(20px);
        }

        .booking-image {
            height: 220px;

            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;

            position: relative;
        }

        .status {
            position: absolute;
            top: 15px;
            right: 15px;

            padding: 7px 13px;

            border-radius: 20px;

            font-size: 11px;
            font-weight: bold;

            background: rgba(0,0,0,.55);

            backdrop-filter: blur(10px);
        }

        .status-PENDING {
            color: #e6cf86;
        }

        .status-CONFIRMED {
            color: #a9d58f;
        }

        .status-CANCELLED {
            color: #e99b9b;
        }

        .booking-content {
            padding: 22px;
        }

        .booking-content h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .booking-location {
            color: #9da49a;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .booking-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .booking-info div {
            padding: 12px;
            border-radius: 12px;
            background: rgba(255,255,255,.04);
        }

        .booking-info small {
            display: block;
            color: #777;
            font-size: 11px;
            margin-bottom: 3px;
        }

        .booking-info strong {
            font-size: 13px;
        }

        .booking-footer {
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,.08);

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-price {
            color: #b5c889;
            font-weight: bold;
        }

        .view-button {
            padding: 9px 15px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.1);
            font-size: 12px;
        }

        .view-button:hover {
            background: rgba(255,255,255,.08);
        }

        .empty-state {
            padding: 60px 30px;
            text-align: center;

            border: 1px solid rgba(255,255,255,.1);
            border-radius: 25px;

            background: rgba(255,255,255,.04);
        }

        .empty-state-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        .empty-state h2 {
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #9da49a;
            margin-bottom: 25px;
        }

        .explore-button {
            display: inline-block;

            padding: 13px 23px;

            border-radius: 25px;

            background: #8b9b62;
            color: #10120d;

            font-weight: bold;
        }

        @media (max-width: 1000px) {

            .booking-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 650px) {

            .booking-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- Navigation -->

<nav class="navbar">

    <a href="../index.php"
       class="logo">

        Explore<span>X</span>

    </a>


    <div class="nav-links">

        <a href="../index.php">
            Home
        </a>

        <a href="../index.php#adventures">
            Adventures
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- Dashboard -->

<main class="dashboard-page">


    <div class="dashboard-header">

        <p class="eyebrow">
            MY EXPLOREX
        </p>

        <h1>

            Welcome,
            <span class="welcome-name">

                <?php
                echo htmlspecialchars(
                    $_SESSION["name"]
                );
                ?>

            </span>

        </h1>

        <p>
            Manage your adventures and bookings
            from one place.
        </p>

    </div>


    <div class="section-heading">

        <p class="eyebrow">
            YOUR JOURNEY
        </p>

        <h2>
            My Bookings
        </h2>

    </div>


    <?php if ($bookings->num_rows > 0): ?>


        <div class="booking-grid">


            <?php while ($booking = $bookings->fetch_assoc()): ?>


                <div class="booking-card">


                    <div
                        class="booking-image"
                        style="background-image:
                            linear-gradient(
                                to top,
                                rgba(0,0,0,.75),
                                transparent
                            ),
                            url('../assets/images/<?php
                                echo htmlspecialchars(
                                    $booking["image_url"]
                                );
                            ?>');"
                    >

                        <span
                            class="status status-<?php
                                echo htmlspecialchars(
                                    $booking["status"]
                                );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $booking["status"]
                            );
                            ?>

                        </span>

                    </div>


                    <div class="booking-content">


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $booking["adventure_name"]
                            );
                            ?>

                        </h3>


                        <p class="booking-location">

                            📍

                            <?php
                            echo htmlspecialchars(
                                $booking["location_name"]
                            );
                            ?>,

                            <?php
                            echo htmlspecialchars(
                                $booking["district"]
                            );
                            ?>

                        </p>


                        <div class="booking-info">


                            <div>

                                <small>
                                    Booking ID
                                </small>

                                <strong>

                                    #

                                    <?php
                                    echo $booking["booking_id"];
                                    ?>

                                </strong>

                            </div>


                            <div>

                                <small>
                                    Participants
                                </small>

                                <strong>

                                    <?php
                                    echo $booking["participants"];
                                    ?>

                                    People

                                </strong>

                            </div>


                            <div>

                                <small>
                                    Booking Date
                                </small>

                                <strong>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $booking["booking_date"]
                                        )
                                    );
                                    ?>

                                </strong>

                            </div>


                            <div>

                                <small>
                                    Status
                                </small>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $booking["status"]
                                    );
                                    ?>

                                </strong>

                            </div>


                        </div>


                        <div class="booking-footer">


                            <span class="booking-price">

                                Rs.

                                <?php
                                echo number_format(
                                    $booking["total_amount"],
                                    2
                                );
                                ?>

                            </span>


                            <a
                                href="adventure-details.php?id=<?php
                                    echo $booking[
                                        "adventure_id"
                                    ];
                                ?>"
                                class="view-button"
                            >
                                View Adventure
                            </a>


                        </div>


                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <div class="empty-state">

            <div class="empty-state-icon">
                🏕️
            </div>

            <h2>
                No Adventures Booked Yet
            </h2>

            <p>
                Your next adventure is waiting for you.
            </p>

            <a
                href="../index.php#adventures"
                class="explore-button"
            >
                Explore Adventures →
            </a>

        </div>


    <?php endif; ?>


</main>

</body>

</html>