<?php

session_start();

require_once "../config/db.php";


// User must be logged in

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");

    exit();
}


// Check adventure ID

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: ../index.php");

    exit();
}


$adventure_id = (int) $_GET["id"];

$user_id = (int) $_SESSION["user_id"];

$message = "";

$message_type = "";


// Get Adventure

$stmt = $conn->prepare("
    SELECT

        a.adventure_id,
        a.adventure_name,
        a.description,
        a.price,
        a.difficulty_level,
        a.duration,
        a.capacity,

        c.category_name,

        l.location_name,
        l.district,

        ai.image_url

    FROM adventures a

    INNER JOIN categories c
        ON a.category_id = c.category_id

    INNER JOIN locations l
        ON a.location_id = l.location_id

    LEFT JOIN adventure_images ai
        ON a.adventure_id = ai.adventure_id
        AND ai.is_main = TRUE

    WHERE a.adventure_id = ?
");


$stmt->bind_param(
    "i",
    $adventure_id
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: ../index.php");

    exit();
}


$adventure = $result->fetch_assoc();


$stmt->close();


// Booking Submit

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $participants =
        (int) $_POST["participants"];


    // Validate participants

    if ($participants < 1) {

        $message =
            "Please select at least 1 participant.";

        $message_type =
            "error";

    } elseif (
        $participants >
        $adventure["capacity"]
    ) {

        $message =
            "Only "
            . $adventure["capacity"]
            . " participants are allowed.";

        $message_type =
            "error";

    } else {


        // Check already booked capacity

        $capacity_stmt = $conn->prepare("
            SELECT
                COALESCE(
                    SUM(participants),
                    0
                ) AS booked

            FROM bookings

            WHERE adventure_id = ?

            AND status IN (
                'PENDING',
                'CONFIRMED'
            )
        ");


        $capacity_stmt->bind_param(
            "i",
            $adventure_id
        );


        $capacity_stmt->execute();


        $capacity_result =
            $capacity_stmt->get_result();


        $capacity_data =
            $capacity_result->fetch_assoc();


        $capacity_stmt->close();


        $already_booked =
            (int) $capacity_data["booked"];


        $remaining =
            $adventure["capacity"]
            - $already_booked;


        if (
            $participants >
            $remaining
        ) {

            $message =
                "Only "
                . $remaining
                . " seats are currently available.";

            $message_type =
                "error";

        } else {


            // Calculate total

            $total_amount =
                $adventure["price"]
                * $participants;


            // Insert booking

            $booking_stmt = $conn->prepare("
                INSERT INTO bookings
                (
                    user_id,
                    adventure_id,
                    booking_date,
                    participants,
                    total_amount,
                    status
                )

                VALUES
                (
                    ?,
                    ?,
                    NOW(),
                    ?,
                    ?,
                    'PENDING'
                )
            ");


            $booking_stmt->bind_param(
                "iiid",
                $user_id,
                $adventure_id,
                $participants,
                $total_amount
            );


            if (
                $booking_stmt->execute()
            ) {

                $booking_id =
                    $booking_stmt->insert_id;


                $booking_stmt->close();


                // Redirect to confirmation

                header(
                    "Location: booking-success.php?id="
                    . $booking_id
                );

                exit();

            } else {

                $message =
                    "Something went wrong. Please try again.";

                $message_type =
                    "error";

                $booking_stmt->close();
            }
        }
    }
}


// Calculate available seats

$capacity_stmt = $conn->prepare("
    SELECT
        COALESCE(
            SUM(participants),
            0
        ) AS booked

    FROM bookings

    WHERE adventure_id = ?

    AND status IN (
        'PENDING',
        'CONFIRMED'
    )
");


$capacity_stmt->bind_param(
    "i",
    $adventure_id
);


$capacity_stmt->execute();


$capacity_result =
    $capacity_stmt->get_result();


$capacity_data =
    $capacity_result->fetch_assoc();


$capacity_stmt->close();


$already_booked =
    (int) $capacity_data["booked"];


$remaining =
    max(
        0,
        $adventure["capacity"]
        - $already_booked
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>

Book
<?php
echo htmlspecialchars(
    $adventure["adventure_name"]
);
?>

| ExploreX

</title>


<link
    rel="stylesheet"
    href="../assets/css/style.css"
>


<style>

.booking-page {

    padding:
        120px
        7%
        80px;

}


.booking-container {

    max-width:
        1100px;

    margin:
        auto;

}


.back-link {

    display:
        inline-block;

    margin-bottom:
        30px;

    color:
        #b5c889;

}


/* MAIN */

.booking-grid {

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        35px;

    align-items:
        start;

}


/* ADVENTURE */

.adventure-card {

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


.adventure-image {

    width:
        100%;

    height:
        350px;

    object-fit:
        cover;

}


.adventure-content {

    padding:
        25px;

}


.category {

    display:
        inline-block;

    padding:
        7px
        12px;

    margin-bottom:
        15px;

    border-radius:
        20px;

    background:
        rgba(181,200,137,.1);

    color:
        #b5c889;

    font-size:
        10px;

    font-weight:
        bold;

}


.adventure-content h1 {

    font-size:
        38px;

    line-height:
        1;

    margin-bottom:
        12px;

}


.location {

    color:
        #9da49a;

    margin-bottom:
        20px;

}


.description {

    color:
        #9da49a;

    line-height:
        1.7;

    font-size:
        13px;

}


/* BOOKING FORM */

.booking-card {

    padding:
        30px;

    border-radius:
        25px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid
        rgba(255,255,255,.1);

}


.booking-card h2 {

    font-size:
        30px;

    margin-bottom:
        8px;

}


.booking-subtitle {

    color:
        #9da49a;

    font-size:
        13px;

    margin-bottom:
        30px;

}


.info-row {

    display:
        flex;

    justify-content:
        space-between;

    padding:
        14px
        0;

    border-bottom:
        1px solid
        rgba(255,255,255,.08);

}


.info-row span:first-child {

    color:
        #888;

    font-size:
        12px;

}


.info-row span:last-child {

    color:
        #d8ddd3;

    font-weight:
        bold;

    font-size:
        13px;

}


/* FORM */

.form-group {

    margin-top:
        25px;

}


.form-group label {

    display:
        block;

    margin-bottom:
        9px;

    font-size:
        13px;

}


.form-group input {

    width:
        100%;

    padding:
        15px;

    border-radius:
        12px;

    border:
        1px solid
        rgba(255,255,255,.12);

    background:
        rgba(255,255,255,.06);

    color:
        white;

    outline:
        none;

    font-size:
        15px;

}


.form-group small {

    display:
        block;

    margin-top:
        7px;

    color:
        #777;

    font-size:
        11px;

}


/* TOTAL */

.total-box {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-top:
        25px;

    padding:
        20px;

    border-radius:
        15px;

    background:
        rgba(181,200,137,.08);

}


.total-box span:first-child {

    color:
        #9da49a;

}


.total-price {

    color:
        #b5c889;

    font-size:
        27px;

    font-weight:
        bold;

}


/* BUTTON */

.book-button {

    width:
        100%;

    margin-top:
        20px;

    padding:
        16px;

    border:
        none;

    border-radius:
        30px;

    background:
        #8b9b62;

    color:
        #10120d;

    font-size:
        16px;

    font-weight:
        bold;

    cursor:
        pointer;

}


.book-button:disabled {

    background:
        #444;

    color:
        #888;

    cursor:
        not-allowed;

}


/* MESSAGE */

.message {

    padding:
        13px;

    margin-bottom:
        20px;

    border-radius:
        12px;

    font-size:
        13px;

}


.message.error {

    color:
        #e99b9b;

    background:
        rgba(233,155,155,.1);

}


/* MOBILE */

@media (max-width: 800px) {

    .booking-grid {

        grid-template-columns:
            1fr;

    }


    .adventure-image {

        height:
            280px;

    }

}

</style>

</head>


<body>


<!-- NAVBAR -->

<!-- SHARED EXPLOREX NAVIGATION -->
<?php
$base_path = "../";
?>
<?php require __DIR__ . "/../includes/navbar.php"; ?>


<main class="booking-page">


<div class="booking-container">


<a
    href="adventure-details.php?id=<?php
        echo $adventure_id;
    ?>"
    class="back-link"
>
    ← Back to Adventure
</a>


<div class="booking-grid">


<!-- ADVENTURE CARD -->

<div class="adventure-card">


<?php if (
    !empty(
        $adventure["image_url"]
    )
): ?>

<img
    src="../assets/images/<?php
        echo htmlspecialchars(
            $adventure["image_url"]
        );
    ?>"
    class="adventure-image"

    alt="<?php
        echo htmlspecialchars(
            $adventure["adventure_name"]
        );
    ?>"
>

<?php endif; ?>


<div class="adventure-content">


<span class="category">

<?php
echo htmlspecialchars(
    $adventure["category_name"]
);
?>

</span>


<h1>

<?php
echo htmlspecialchars(
    $adventure["adventure_name"]
);
?>

</h1>


<p class="location">

📍

<?php
echo htmlspecialchars(
    $adventure["location_name"]
);
?>

,

<?php
echo htmlspecialchars(
    $adventure["district"]
);
?>

</p>


<p class="description">

<?php
echo nl2br(
    htmlspecialchars(
        $adventure["description"]
    )
);
?>

</p>


</div>

</div>


<!-- BOOKING CARD -->

<div class="booking-card">


<h2>
    Complete Your Booking
</h2>


<p class="booking-subtitle">
    Choose the number of participants.
</p>


<?php if (
    !empty($message)
): ?>

<div class="message <?php
    echo $message_type;
?>">

<?php
echo htmlspecialchars(
    $message
);
?>

</div>

<?php endif; ?>


<!-- DETAILS -->


<div class="info-row">

<span>
    Price per person
</span>

<span>

Rs.

<?php
echo number_format(
    $adventure["price"],
    2
);
?>

</span>

</div>


<div class="info-row">

<span>
    Maximum capacity
</span>

<span>

<?php
echo $adventure["capacity"];
?>

 people

</span>

</div>


<div class="info-row">

<span>
    Available seats
</span>

<span>

<?php
echo $remaining;
?>

 people

</span>

</div>


<div class="info-row">

<span>
    Difficulty
</span>

<span>

<?php
echo htmlspecialchars(
    $adventure[
        "difficulty_level"
    ]
);
?>

</span>

</div>


<!-- FORM -->

<form
    method="POST"
    id="bookingForm"
>


<div class="form-group">


<label
    for="participants"
>
    Number of Participants
</label>


<input
    type="number"

    id="participants"

    name="participants"

    min="1"

    max="<?php
        echo $remaining;
    ?>"

    value="1"

    required
>


<small>

Maximum
<?php
echo $remaining;
?>
participants available.

</small>


</div>


<!-- TOTAL -->

<div class="total-box">

<span>
    Total Amount
</span>


<span
    class="total-price"
    id="totalPrice"
>

Rs.

<?php
echo number_format(
    $adventure["price"],
    2
);
?>

</span>


</div>


<?php if (
    $remaining > 0
): ?>


<button
    type="submit"
    class="book-button"
    id="bookButton"
>
    Confirm Booking →
</button>


<?php else: ?>


<button
    type="button"
    class="book-button"
    disabled
>
    Fully Booked
</button>


<?php endif; ?>


</form>


</div>


</div>


</div>

</main>


<script>

const participantsInput =
    document.getElementById(
        "participants"
    );

const totalPrice =
    document.getElementById(
        "totalPrice"
    );


const pricePerPerson =
    <?php
    echo (float)
        $adventure["price"];
    ?>;


function calculateTotal() {

    let participants =
        parseInt(
            participantsInput.value
        ) || 0;


    if (participants < 1) {

        participants = 1;

    }


    const total =
        pricePerPerson *
        participants;


    totalPrice.textContent =
        "Rs. "
        + total.toLocaleString(
            "en-LK",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );

}


participantsInput.addEventListener(
    "input",
    calculateTotal
);


calculateTotal();

</script>


</body>

</html>