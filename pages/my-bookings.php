<?php

session_start();

require_once "../config/db.php";


// --------------------------------------------------
// USER LOGIN CHECK
// --------------------------------------------------

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit();

}


// --------------------------------------------------
// ADMIN SHOULD NOT ACCESS USER BOOKINGS
// --------------------------------------------------

if ($_SESSION["role"] !== "USER") {

    header("Location: ../admin/dashboard.php");
    exit();

}


$user_id = $_SESSION["user_id"];


// --------------------------------------------------
// GET USER BOOKINGS
// --------------------------------------------------

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

    <title>My Bookings | ExploreX</title>


    <!-- SAME MAIN CSS -->

    <link rel="stylesheet"
          href="../assets/css/style.css">


    <style>
/* ==========================================
           MY BOOKINGS PAGE
        ========================================== */

        .bookings-page {

            width: 100%;

            max-width: 1100px;

            margin: 0 auto;

            padding: 130px 30px 80px;

        }


        /* ==========================================
           PAGE HEADER
        ========================================== */

        .bookings-header {

            margin-bottom: 35px;

        }


        .bookings-header h1 {

            font-size: clamp(40px, 5vw, 62px);

            line-height: 1;

            margin: 8px 0 12px;

        }


        .bookings-header p {

            color: #9da49a;

        }


        /* ==========================================
           BOOKING LIST
        ========================================== */

        .booking-list {

            display: flex;

            flex-direction: column;

            gap: 18px;

            width: 100%;

        }


        /* ==========================================
           HORIZONTAL BOOKING CARD
        ========================================== */

        .booking-card {

            position: relative;

            width: 100%;

            min-height: 178px;

            display: flex;

            align-items: center;

            gap: 20px;

            padding: 18px;

            overflow: hidden;

            border: 1px solid rgba(255,255,255,.10);

            border-radius: 20px;

            background: rgba(255,255,255,.05);

            backdrop-filter: blur(20px);

            transition: .25s ease;

        }


        .booking-card:hover {

            transform: translateY(-2px);

            border-color: rgba(181,200,137,.25);

        }

.booking-card:hover {

            transform: translateY(-2px);

            border-color: rgba(181,200,137,.25);

        }


        /* ==========================================
           IMAGE
        ========================================== */

        .booking-image {

            width: 175px;

            min-width: 175px;

            height: 140px;

            border-radius: 14px;

            background-position: center;

            background-size: cover;

            background-repeat: no-repeat;

            position: relative;

            overflow: hidden;

        }
/* ==========================================
           STATUS
        ========================================== */

        .status {

            position: absolute;

            top: 18px;

            right: 18px;

            padding: 7px 13px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: .4px;

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


        /* ==========================================
           BOOKING CONTENT
        ========================================== */

        .booking-content {

            flex: 1;

            padding: 8px 130px 8px 0;

            display: flex;

            flex-direction: column;

            min-width: 0;

        }


        /* ==========================================
           TITLE
        ========================================== */

        .booking-title {

            font-size: 23px;

            font-weight: 700;

            margin: 0 0 6px;

            color: #f5f5f5;

        }


        /* ==========================================
           LOCATION
        ========================================== */

        .booking-location {

            color: #9da49a;

            font-size: 12px;

            margin: 0 0 17px;

        }


        /* ==========================================
           INFORMATION
        ========================================== */

        .booking-details {

            display: grid;

            grid-template-columns: 1fr 1fr;

            column-gap: 70px;

            row-gap: 10px;

            flex: 1;

        }


        .booking-detail {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .booking-detail small {

            color: #777;

            font-size: 9px;

            text-transform: uppercase;

            letter-spacing: .5px;

        }


        .booking-detail strong {

            color: #e7e7e7;

            font-size: 13px;

        }


        /* ==========================================
           PRICE
        ========================================== */

        .booking-price {

            color: #b5c889 !important;

            font-size: 14px !important;

        }


        /* ==========================================
           FOOTER
        ========================================== */

        .booking-footer {

            margin-top: 10px;

            padding-top: 10px;

            border-top:

                1px solid

                rgba(255,255,255,.08);

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .booked-date {

            color: #777;

            font-size: 10px;

        }


        .view-button {

            display: inline-block;

            padding: 8px 15px;

            border-radius: 20px;

            border:

                1px solid

                rgba(255,255,255,.12);

            color: #ddd;

            font-size: 10px;

            text-decoration: none;

            transition: .2s ease;

        }


        .view-button:hover {

            background: rgba(255,255,255,.08);

            color: #fff;

        }


        /* ==========================================
           EMPTY STATE
        ========================================== */

        .empty-state {

            width: 100%;

            padding: 60px 30px;

            text-align: center;

            border:

                1px solid

                rgba(255,255,255,.1);

            border-radius: 20px;

            background: rgba(255,255,255,.04);

        }


        .empty-state-icon {

            font-size: 45px;

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

            padding: 12px 22px;

            border-radius: 25px;

            background: #8b9b62;

            color: #10120d;

            font-weight: bold;

            text-decoration: none;

        }


        /* ==========================================
           TABLET
        ========================================== */

        @media (max-width: 800px) {

            .bookings-page {

                padding-left: 20px;

                padding-right: 20px;

            }


            .booking-image {

                width: 160px;

                min-width: 160px;

            }


            .booking-details {

                column-gap: 25px;

            }

        }


        /* ==========================================
           MOBILE
        ========================================== */

        @media (max-width: 600px) {

            .bookings-page {

                padding-top: 110px;

            }


            .booking-card {

                min-height: 0;

                flex-direction: column;

            }


            .booking-image {

                width: 100%;

                min-width: 100%;

                height: 180px;

            }

            .booking-card {

                align-items: stretch;

                gap: 0;

                padding: 14px;

            }


            .booking-content {

                padding: 18px 4px 4px;

            }


            .booking-title {

                font-size: 20px;

            }


            .booking-details {

                column-gap: 15px;

            }

        }

    </style>

</head>


<body>


<!-- ==================================================
     USER NAVIGATION
     NO DASHBOARD
================================================== -->

<!-- SHARED EXPLOREX NAVIGATION -->
<?php
$base_path = "../";
?>
<?php require __DIR__ . "/../includes/navbar.php"; ?>


<!-- ==================================================
     MY BOOKINGS
================================================== -->

<main class="bookings-page">


    <div class="bookings-header">


        <p class="eyebrow">

            YOUR BOOKINGS

        </p>


        <h1>

            My Bookings

        </h1>


        <p>

            Keep track of all your ExploreX adventures.

        </p>


    </div>



    <?php if ($bookings->num_rows > 0): ?>


        <div class="booking-list">


            <?php while ($booking = $bookings->fetch_assoc()): ?>


                <div class="booking-card">

                    <!-- IMAGE -->
                    <div
                        class="booking-image"
                        style="background-image: url('../assets/images/<?php echo htmlspecialchars($booking["image_url"]); ?>');"
                    ></div>

                    <!-- STATUS -->
                    <span
                        class="status status-<?php echo htmlspecialchars($booking["status"]); ?>"
                    >
                        <?php echo htmlspecialchars($booking["status"]); ?>
                    </span>

                    <!-- CONTENT -->

                    <div class="booking-content">


                        <h2 class="booking-title">

                            <?php

                            echo htmlspecialchars(

                                $booking["adventure_name"]

                            );

                            ?>

                        </h2>


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



                        <!-- DETAILS -->

                        <div class="booking-details">


                            <div class="booking-detail">

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



                            <div class="booking-detail">

                                <small>

                                    Participants

                                </small>

                                <strong>

                                    <?php

                                    echo $booking["participants"];

                                    ?>

                                    people

                                </strong>

                            </div>



                            <div class="booking-detail">

                                <small>

                                    Total Amount

                                </small>

                                <strong
                                    class="booking-price"
                                >

                                    Rs.

                                    <?php

                                    echo number_format(

                                        $booking["total_amount"],

                                        2

                                    );

                                    ?>

                                </strong>

                            </div>



                            <div class="booking-detail">

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



                        <!-- FOOTER -->

                        <div class="booking-footer">


                            <span class="booked-date">

                                Booked on:

                                <?php

                                echo date(

                                    "d M Y, h:i A",

                                    strtotime(

                                        $booking["booking_date"]

                                    )

                                );

                                ?>

                            </span>



                            <a
                                href="adventure-details.php?id=<?php

                                    echo $booking["adventure_id"];

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