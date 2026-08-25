<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "ADMIN") {
    header("Location: ../index.php");
    exit();
}


// Statistics

$user_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
");

$total_users = $user_result->fetch_assoc()["total"];


$adventure_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM adventures
");

$total_adventures =
    $adventure_result->fetch_assoc()["total"];


$booking_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
");

$total_bookings =
    $booking_result->fetch_assoc()["total"];


$pending_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE status = 'PENDING'
");

$pending_bookings =
    $pending_result->fetch_assoc()["total"];


// Recent bookings

$recent_stmt = $conn->query("
    SELECT
        b.booking_id,
        b.booking_date,
        b.participants,
        b.total_amount,
        b.status,

        u.name AS user_name,

        a.adventure_name

    FROM bookings b

    INNER JOIN users u
        ON b.user_id = u.user_id

    INNER JOIN adventures a
        ON b.adventure_id = a.adventure_id

    ORDER BY b.booking_date DESC

    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Admin Dashboard | ExploreX
    </title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .admin-page {
            padding: 130px 7% 80px;
        }

        .admin-header {
            margin-bottom: 45px;
        }

        .admin-header h1 {
            font-size: clamp(40px, 5vw, 65px);
            line-height: 1;
            margin-bottom: 12px;
        }

        .admin-header p {
            color: #9da49a;
        }

        .admin-name {
            color: #b5c889;
        }


        /* STATISTICS */

        .stats-grid {
            display: grid;
            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 55px;
        }

        .stat-card {
            padding: 25px;

            border-radius: 22px;

            background:
                rgba(255,255,255,.05);

            border:
                1px solid
                rgba(255,255,255,.1);

            backdrop-filter: blur(20px);
        }

        .stat-card p {
            color: #9da49a;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .stat-card h2 {
            font-size: 38px;
            color: #b5c889;
        }


        /* QUICK ACTIONS */

        .actions-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;

            margin-bottom: 60px;
        }

        .action-card {
            display: block;

            padding: 25px;

            border-radius: 22px;

            background:
                rgba(255,255,255,.05);

            border:
                1px solid
                rgba(255,255,255,.1);

            transition: .3s;
        }

        .action-card:hover {
            transform: translateY(-5px);

            border-color:
                rgba(181,200,137,.35);
        }

        .action-icon {
            font-size: 30px;

            margin-bottom: 15px;
        }

        .action-card h3 {
            margin-bottom: 5px;
        }

        .action-card p {
            color: #9da49a;
            font-size: 13px;
        }


        /* RECENT BOOKINGS */

        .table-wrapper {
            overflow-x: auto;

            border:
                1px solid
                rgba(255,255,255,.1);

            border-radius: 20px;
        }

        .booking-table {
            width: 100%;

            border-collapse: collapse;

            min-width: 750px;
        }

        .booking-table th {
            text-align: left;

            padding: 18px;

            color: #b5c889;

            font-size: 12px;

            text-transform: uppercase;

            letter-spacing: 1px;

            background:
                rgba(255,255,255,.04);
        }

        .booking-table td {
            padding: 18px;

            border-top:
                1px solid
                rgba(255,255,255,.07);

            color: #d8ddd3;

            font-size: 13px;
        }

        .status {
            padding: 6px 11px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: bold;
        }

        .status-PENDING {
            color: #e6cf86;

            background:
                rgba(230,207,134,.1);
        }

        .status-CONFIRMED {
            color: #a9d58f;

            background:
                rgba(169,213,143,.1);
        }

        .status-CANCELLED {
            color: #e99b9b;

            background:
                rgba(233,155,155,.1);
        }


        @media (max-width: 1000px) {

            .stats-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .actions-grid {
                grid-template-columns:
                    1fr 1fr;
            }

        }


        @media (max-width: 600px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>


<!-- NAVIGATION -->

<!-- SHARED EXPLOREX NAVIGATION -->
<?php
$base_path = "../";
?>
<?php require __DIR__ . "/../includes/navbar.php"; ?>


<!-- ADMIN -->

<main class="admin-page">


    <div class="admin-header">

        <p class="eyebrow">
            EXPLOREX ADMIN
        </p>

        <h1>

            Welcome,
            <span class="admin-name">

                <?php
                echo htmlspecialchars(
                    $_SESSION["name"]
                );
                ?>

            </span>

        </h1>

        <p>
            Manage your ExploreX platform
            from one place.
        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats-grid">


        <div class="stat-card">

            <p>
                TOTAL USERS
            </p>

            <h2>
                <?php echo $total_users; ?>
            </h2>

        </div>


        <div class="stat-card">

            <p>
                ADVENTURES
            </p>

            <h2>
                <?php echo $total_adventures; ?>
            </h2>

        </div>


        <div class="stat-card">

            <p>
                TOTAL BOOKINGS
            </p>

            <h2>
                <?php echo $total_bookings; ?>
            </h2>

        </div>


        <div class="stat-card">

            <p>
                PENDING BOOKINGS
            </p>

            <h2>
                <?php echo $pending_bookings; ?>
            </h2>

        </div>


    </div>


    <!-- QUICK ACTIONS -->

    <div class="section-heading">

        <p class="eyebrow">
            MANAGEMENT
        </p>

        <h2>
            Quick Actions
        </h2>

    </div>


    <div class="actions-grid">


        <a
            href="add-adventure.php"
            class="action-card"
        >

            <div class="action-icon">
                ➕
            </div>

            <h3>
                Add Adventure
            </h3>

            <p>
                Create a new adventure
                destination.
            </p>

        </a>


        <a
            href="manage-adventures.php"
            class="action-card"
        >

            <div class="action-icon">
                🏕️
            </div>

            <h3>
                Manage Adventures
            </h3>

            <p>
                Edit or delete existing
                adventures.
            </p>

        </a>


        <a
            href="manage-bookings.php"
            class="action-card"
        >

            <div class="action-icon">
                📋
            </div>

            <h3>
                Manage Bookings
            </h3>

            <p>
                View and manage user
                bookings.
            </p>

        </a>


    </div>


    <!-- RECENT BOOKINGS -->

    <div class="section-heading">

        <p class="eyebrow">
            ACTIVITY
        </p>

        <h2>
            Recent Bookings
        </h2>

    </div>


    <div class="table-wrapper">

        <table class="booking-table">

            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        User
                    </th>

                    <th>
                        Adventure
                    </th>

                    <th>
                        Participants
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Date
                    </th>

                </tr>

            </thead>


            <tbody>


                <?php if ($recent_stmt->num_rows > 0): ?>


                    <?php while (
                        $booking =
                        $recent_stmt->fetch_assoc()
                    ): ?>


                        <tr>

                            <td>

                                #
                                <?php
                                echo $booking[
                                    "booking_id"
                                ];
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $booking[
                                        "user_name"
                                    ]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $booking[
                                        "adventure_name"
                                    ]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo $booking[
                                    "participants"
                                ];
                                ?>

                            </td>


                            <td>

                                Rs.

                                <?php
                                echo number_format(
                                    $booking[
                                        "total_amount"
                                    ],
                                    2
                                );
                                ?>

                            </td>


                            <td>

                                <span
                                    class="status status-<?php
                                        echo htmlspecialchars(
                                            $booking[
                                                "status"
                                            ]
                                        );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $booking[
                                            "status"
                                        ]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $booking[
                                            "booking_date"
                                        ]
                                    )
                                );
                                ?>

                            </td>

                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="7"
                            style="
                                text-align:center;
                                padding:40px;
                                color:#9da49a;
                            "
                        >

                            No bookings yet.

                        </td>

                    </tr>


                <?php endif; ?>


            </tbody>

        </table>

    </div>


</main>

</body>

</html>