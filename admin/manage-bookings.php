<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "ADMIN") {
    header("Location: ../pages/dashboard.php");
    exit();
}


// Update booking status

if (
    isset($_GET["action"]) &&
    isset($_GET["id"]) &&
    is_numeric($_GET["id"])
) {

    $booking_id = (int) $_GET["id"];

    $action = strtoupper(
        trim($_GET["action"])
    );


    $allowed_statuses = [
        "CONFIRMED",
        "CANCELLED"
    ];


    if (
        in_array(
            $action,
            $allowed_statuses
        )
    ) {

        $stmt = $conn->prepare("
            UPDATE bookings

            SET status = ?

            WHERE booking_id = ?
        ");

        $stmt->bind_param(
            "si",
            $action,
            $booking_id
        );

        $stmt->execute();

        $stmt->close();
    }


    header(
        "Location: manage-bookings.php?updated=1"
    );

    exit();
}


// Get bookings

$stmt = $conn->prepare("
    SELECT

        b.booking_id,
        b.booking_date,
        b.participants,
        b.total_amount,
        b.status,

        u.user_id,
        u.name AS user_name,
        u.email AS user_email,

        a.adventure_id,
        a.adventure_name,

        l.location_name,
        l.district

    FROM bookings b

    INNER JOIN users u
        ON b.user_id = u.user_id

    INNER JOIN adventures a
        ON b.adventure_id = a.adventure_id

    INNER JOIN locations l
        ON a.location_id = l.location_id

    ORDER BY
        b.booking_date DESC
");

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
    Manage Bookings | ExploreX
</title>

<link rel="stylesheet"
      href="../assets/css/style.css">


<style>

.manage-page {

    padding:
        130px
        7%
        80px;
}


.manage-header {

    margin-bottom:
        40px;
}


.manage-header h1 {

    font-size:
        55px;

    line-height:
        1;

}


.manage-header p {

    color:
        #9da49a;

    margin-top:
        10px;
}


.message {

    padding:
        13px;

    margin-bottom:
        25px;

    border-radius:
        12px;

    background:
        rgba(169,213,143,.1);

    color:
        #a9d58f;
}


/* TABLE */

.table-wrapper {

    overflow-x:
        auto;

    border:
        1px solid
        rgba(255,255,255,.1);

    border-radius:
        22px;

    background:
        rgba(255,255,255,.04);
}


.booking-table {

    width:
        100%;

    min-width:
        1100px;

    border-collapse:
        collapse;
}


.booking-table th {

    text-align:
        left;

    padding:
        18px;

    font-size:
        11px;

    color:
        #b5c889;

    text-transform:
        uppercase;

    letter-spacing:
        1px;

    background:
        rgba(255,255,255,.05);
}


.booking-table td {

    padding:
        18px;

    border-top:
        1px solid
        rgba(255,255,255,.07);

    font-size:
        13px;

    color:
        #d8ddd3;

    vertical-align:
        middle;
}


.booking-id {

    color:
        #b5c889;

    font-weight:
        bold;
}


.user-name {

    font-weight:
        bold;

    margin-bottom:
        4px;
}


.user-email {

    color:
        #777;

    font-size:
        11px;
}


.adventure-name {

    font-weight:
        bold;

    margin-bottom:
        4px;
}


.location {

    color:
        #888;

    font-size:
        11px;
}


/* STATUS */

.status {

    display:
        inline-block;

    padding:
        7px
        12px;

    border-radius:
        20px;

    font-size:
        10px;

    font-weight:
        bold;
}


.status-PENDING {

    color:
        #e6cf86;

    background:
        rgba(230,207,134,.1);
}


.status-CONFIRMED {

    color:
        #a9d58f;

    background:
        rgba(169,213,143,.1);
}


.status-CANCELLED {

    color:
        #e99b9b;

    background:
        rgba(233,155,155,.1);
}


/* ACTIONS */

.actions {

    display:
        flex;

    gap:
        7px;

    flex-wrap:
        wrap;
}


.action-button {

    display:
        inline-block;

    padding:
        7px
        11px;

    border-radius:
        18px;

    font-size:
        10px;

    font-weight:
        bold;
}


.confirm-button {

    color:
        #a9d58f;

    border:
        1px solid
        rgba(169,213,143,.25);
}


.cancel-button {

    color:
        #e99b9b;

    border:
        1px solid
        rgba(233,155,155,.25);
}


.disabled-button {

    color:
        #666;

    border:
        1px solid
        rgba(255,255,255,.08);
}


.empty-state {

    padding:
        60px;

    text-align:
        center;

    color:
        #9da49a;
}

</style>

</head>


<body>


<!-- NAVIGATION -->

<nav class="navbar">

    <a
        href="../index.php"
        class="logo"
    >

        Explore<span>X</span>

    </a>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="manage-adventures.php">
            Adventures
        </a>

        <a href="../index.php">
            Website
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<main class="manage-page">


<div class="manage-header">

    <p class="eyebrow">
        ADMIN MANAGEMENT
    </p>

    <h1>
        Bookings
    </h1>

    <p>
        Review and manage customer bookings.
    </p>

</div>


<?php if (
    isset($_GET["updated"])
): ?>

<div class="message">

    Booking status updated successfully.

</div>

<?php endif; ?>


<?php if (
    $bookings->num_rows > 0
): ?>


<div class="table-wrapper">


<table class="booking-table">


<thead>

<tr>

    <th>
        Booking
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
        Date
    </th>

    <th>
        Status
    </th>

    <th>
        Actions
    </th>

</tr>

</thead>


<tbody>


<?php while (
    $booking =
    $bookings->fetch_assoc()
): ?>


<tr>


<!-- Booking -->

<td>

    <div class="booking-id">

        #

        <?php
        echo $booking[
            "booking_id"
        ];
        ?>

    </div>

</td>


<!-- User -->

<td>

    <div class="user-name">

        <?php
        echo htmlspecialchars(
            $booking[
                "user_name"
            ]
        );
        ?>

    </div>

    <div class="user-email">

        <?php
        echo htmlspecialchars(
            $booking[
                "user_email"
            ]
        );
        ?>

    </div>

</td>


<!-- Adventure -->

<td>

    <div class="adventure-name">

        <?php
        echo htmlspecialchars(
            $booking[
                "adventure_name"
            ]
        );
        ?>

    </div>

    <div class="location">

        📍

        <?php
        echo htmlspecialchars(
            $booking[
                "location_name"
            ]
        );
        ?>,

        <?php
        echo htmlspecialchars(
            $booking[
                "district"
            ]
        );
        ?>

    </div>

</td>


<!-- Participants -->

<td>

    <?php
    echo $booking[
        "participants"
    ];
    ?>

    people

</td>


<!-- Amount -->

<td>

    <strong>

        Rs.

        <?php
        echo number_format(
            $booking[
                "total_amount"
            ],
            2
        );
        ?>

    </strong>

</td>


<!-- Date -->

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

    <br>

    <small style="color:#777;">

        <?php
        echo date(
            "h:i A",
            strtotime(
                $booking[
                    "booking_date"
                ]
            )
        );
        ?>

    </small>

</td>


<!-- Status -->

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


<!-- Actions -->

<td>

<div class="actions">


<?php if (
    $booking["status"]
    === "PENDING"
): ?>


<a
    href="manage-bookings.php?action=CONFIRMED&id=<?php
        echo $booking[
            "booking_id"
        ];
    ?>"
    class="action-button confirm-button"

    onclick="
        return confirm(
            'Confirm this booking?'
        );
    "
>
    Confirm
</a>


<a
    href="manage-bookings.php?action=CANCELLED&id=<?php
        echo $booking[
            "booking_id"
        ];
    ?>"
    class="action-button cancel-button"

    onclick="
        return confirm(
            'Cancel this booking?'
        );
    "
>
    Cancel
</a>


<?php else: ?>


<span
    class="action-button disabled-button"
>
    Completed
</span>


<?php endif; ?>


</div>

</td>


</tr>


<?php endwhile; ?>


</tbody>

</table>


</div>


<?php else: ?>


<div class="empty-state">

    <h2>
        No Bookings Yet
    </h2>

    <p>
        Customer bookings will appear here.
    </p>

</div>


<?php endif; ?>


</main>

</body>

</html>