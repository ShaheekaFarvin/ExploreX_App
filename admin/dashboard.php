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
            font-size: clamp(34px, 4vw, 52px);
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
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 48px;
        }

        .stat-card {
            position: relative;
            min-height: 142px;
            padding: 20px 16px;
            border-radius: 18px;
            overflow: hidden;
            text-decoration: none;
            text-align: center;
            background-size: cover;
            background-position: center;
            border: 1px solid rgba(255,255,255,.12);
            box-shadow: 0 12px 30px rgba(0,0,0,.18);
            backdrop-filter: blur(12px);
            transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(181,200,137,.45);
            box-shadow: 0 18px 38px rgba(0,0,0,.28);
        }

        .stat-users {
            background-image: url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=700&q=80');
        }

        .stat-adventures {
            background-image: url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=700&q=80');
        }

        .stat-bookings {
            background-image: url('https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=700&q=80');
        }

        .stat-pending {
            background-image: url('https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=700&q=80');
        }

        .stat-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(8,12,9,.78), rgba(24,30,20,.55));
        }

        .stat-content {
            position: relative;
            z-index: 1;
        }

        .stat-card p {
            margin: 0 0 7px;
            color: #eef2e8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.1px;
        }

        .stat-card h2 {
            margin: 0;
            color: #d6e3ad;
            font-size: 34px;
            line-height: 1;
            font-weight: 800;
        }

        .stat-card span {
            display: block;
            margin-top: 10px;
            color: rgba(255,255,255,.72);
            font-size: 10px;
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


    <!-- PLATFORM STATISTICS -->

    <div class="stats-grid">

        <a href="manage-users.php" class="stat-card stat-users">
            <div class="stat-overlay"></div>
            <div class="stat-content">
                <p>TOTAL USERS</p>
                <h2><?php echo (int)$total_users; ?></h2>
                <span>View users →</span>
            </div>
        </a>

        <a href="manage-adventures.php" class="stat-card stat-adventures">
            <div class="stat-overlay"></div>
            <div class="stat-content">
                <p>ADVENTURES</p>
                <h2><?php echo (int)$total_adventures; ?></h2>
                <span>Manage adventures →</span>
            </div>
        </a>

        <a href="manage-bookings.php" class="stat-card stat-bookings">
            <div class="stat-overlay"></div>
            <div class="stat-content">
                <p>TOTAL BOOKINGS</p>
                <h2><?php echo (int)$total_bookings; ?></h2>
                <span>View bookings →</span>
            </div>
        </a>

        <a href="manage-bookings.php?status=PENDING" class="stat-card stat-pending">
            <div class="stat-overlay"></div>
            <div class="stat-content">
                <p>PENDING BOOKINGS</p>
                <h2><?php echo (int)$pending_bookings; ?></h2>
                <span>Review pending →</span>
            </div>
        </a>

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


    <div class="actions-grid admin-visual-actions">

        <a href="add-adventure.php" class="action-card action-adventure-add">
            <div class="action-icon">＋</div>
            <div class="action-copy">
                <h3>ADD ADVENTURE</h3>
                <p>Create a new destination.</p>
            </div>
        </a>

        <a href="manage-adventures.php" class="action-card action-adventure-manage">
            <div class="action-icon">✦</div>
            <div class="action-copy">
                <h3>MANAGE ADVENTURES</h3>
                <p>Edit and organise experiences.</p>
            </div>
        </a>

        <a href="manage-bookings.php" class="action-card action-booking-manage">
            <div class="action-icon">⌁</div>
            <div class="action-copy">
                <h3>MANAGE BOOKINGS</h3>
                <p>Review and update reservations.</p>
            </div>
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