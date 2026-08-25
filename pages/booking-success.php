<?php

session_start();

require_once "../config/db.php";


// User must be logged in

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}


// Check booking ID

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: dashboard.php");
    exit();
}


$booking_id = (int) $_GET["id"];

$user_id = (int) $_SESSION["user_id"];


// Get booking details

$stmt = $conn->prepare("
    SELECT

        b.booking_id,
        b.booking_date,
        b.participants,
        b.total_amount,
        b.status,

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

    WHERE
        b.booking_id = ?
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

    $stmt->close();

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

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Booking Confirmed | ExploreX
</title>

<link
    rel="stylesheet"
    href="../assets/css/style.css"
>


<style>

.success-page {

    min-height:
        100vh;

    padding:
        120px 7% 70px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.success-container {

    width:
        100%;

    max-width:
        850px;

    text-align:
        center;

}


.success-icon {

    width:
        80px;

    height:
        80px;

    margin:
        0 auto 25px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        50%;

    background:
        rgba(169,213,143,.12);

    border:
        1px solid
        rgba(169,213,143,.3);

    color:
        #a9d58f;

    font-size:
        38px;

}


.success-container h1 {

    font-size:
        clamp(42px, 6vw, 70px);

    line-height:
        .95;

    margin-bottom:
        15px;

}


.success-container > p {

    color:
        #9da49a;

    margin-bottom:
        35px;

}


.booking-card {

    text-align:
        left;

    overflow:
        hidden;

    border-radius:
        25px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid
        rgba(255,255,255,.1);

}


.booking-top {

    display:
        grid;

    grid-template-columns:
        180px 1fr;

    gap:
        25px;

    padding:
        25px;

}


.booking-image {

    width:
        180px;

    height:
        140px;

    object-fit:
        cover;

    border-radius:
        16px;

}


.adventure-info h2 {

    font-size:
        25px;

    margin-bottom:
        8px;

}


.location {

    color:
        #9da49a;

    font-size:
        13px;

}


.booking-id {

    margin-top:
        15px;

    color:
        #b5c889;

    font-size:
        12px;

}


.booking-details {

    display:
        grid;

    grid-template-columns:
        repeat(3, 1fr);

    border-top:
        1px solid
        rgba(255,255,255,.08);

}


.detail-box {

    padding:
        20px;

    border-right:
        1px solid
        rgba(255,255,255,.08);

}


.detail-box:last-child {

    border-right:
        none;

}


.detail-box small {

    display:
        block;

    color:
        #777;

    font-size:
        10px;

    text-transform:
        uppercase;

    margin-bottom:
        7px;

}


.detail-box strong {

    font-size:
        15px;

}


.total {

    color:
        #b5c889;

    font-size:
        20px;

}


.status {

    display:
        inline-block;

    padding:
        7px 13px;

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


.actions {

    display:
        flex;

    justify-content:
        center;

    gap:
        12px;

    margin-top:
        30px;

}


.button {

    display:
        inline-block;

    padding:
        14px 25px;

    border-radius:
        30px;

    font-size:
        13px;

    font-weight:
        bold;

}


.primary-button {

    background:
        #8b9b62;

    color:
        #10120d;

}


.secondary-button {

    border:
        1px solid
        rgba(255,255,255,.15);

    color:
        #d8ddd3;

}


@media (max-width: 650px) {

    .booking-top {

        grid-template-columns:
            1fr;

    }


    .booking-image {

        width:
            100%;

        height:
            220px;

    }


    .booking-details {

        grid-template-columns:
            1fr;

    }


    .detail-box {

        border-right:
            none;

        border-bottom:
            1px solid
            rgba(255,255,255,.08);

    }


    .detail-box:last-child {

        border-bottom:
            none;

    }


    .actions {

        flex-direction:
            column;

    }


    .button {

        text-align:
            center;

    }

}

</style>

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar">

    <a
        href="../index.php"
        class="logo"
    >

        Explore<span>X</span>

    </a>


    <div class="nav-links">

        <a href="../index.php">
            Explore
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- SUCCESS -->

<main class="success-page">


<div class="success-container">


<div class="success-icon">

    ✓

</div>


<h1>
    Booking Received!
</h1>


<p>

Your adventure booking has been successfully
submitted and is waiting for confirmation.

</p>


<div class="booking-card">


<!-- ADVENTURE -->

<div class="booking-top">


<?php if (
    !empty($booking["image_url"])
): ?>

<img
    src="../assets/images/<?php
        echo htmlspecialchars(
            $booking["image_url"]
        );
    ?>"
    class="booking-image"

    alt="<?php
        echo htmlspecialchars(
            $booking["adventure_name"]
        );
    ?>"
>

<?php endif; ?>


<div class="adventure-info">


<h2>

<?php
echo htmlspecialchars(
    $booking["adventure_name"]
);
?>

</h2>


<p class="location">

📍

<?php
echo htmlspecialchars(
    $booking["location_name"]
);
?>

,

<?php
echo htmlspecialchars(
    $booking["district"]
);
?>

</p>


<p class="booking-id">

Booking ID:

<strong>

#

<?php
echo $booking["booking_id"];
?>

</strong>

</p>


</div>


</div>


<!-- DETAILS -->

<div class="booking-details">


<div class="detail-box">

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


<div class="detail-box">

<small>
    Total Amount
</small>

<strong class="total">

Rs.

<?php
echo number_format(
    $booking["total_amount"],
    2
);
?>

</strong>

</div>


<div class="detail-box">

<small>
    Status
</small>

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


</div>


</div>


<!-- ACTIONS -->

<div class="actions">


<a
    href="dashboard.php"
    class="button primary-button"
>
    View My Bookings
</a>


<a
    href="../index.php"
    class="button secondary-button"
>
    Explore More
</a>


</div>


</div>

</main>


</body>

</html>