<?php

session_start();

require_once "../config/db.php";


// Check adventure ID

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../index.php");
    exit();
}

$adventure_id = (int) $_GET["id"];


// Get adventure details

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


// Adventure not found

if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: ../index.php");

    exit();
}


$adventure = $result->fetch_assoc();

$stmt->close();

// Load every image for the adventure so the details page has a gallery.
$image_stmt = $conn->prepare("
    SELECT image_url, is_main
    FROM adventure_images
    WHERE adventure_id = ?
    ORDER BY is_main DESC, image_url ASC
");
$image_stmt->bind_param("i", $adventure_id);
$image_stmt->execute();
$image_result = $image_stmt->get_result();
$gallery_images = [];
while ($image_row = $image_result->fetch_assoc()) {
    if (!empty($image_row["image_url"])) {
        $gallery_images[] = $image_row["image_url"];
    }
}
$image_stmt->close();

if (empty($gallery_images) && !empty($adventure["image_url"])) {
    $gallery_images[] = $adventure["image_url"];
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>

<?php
echo htmlspecialchars(
    $adventure["adventure_name"]
);
?>

| ExploreX

</title>


<link rel="stylesheet"
      href="../assets/css/style.css">


<style>

.details-page {

    padding:
        120px
        7%
        80px;

}


/* BACK */

.back-link {

    display:
        inline-block;

    margin-bottom:
        30px;

    color:
        #b5c889;

}


/* MAIN */

.details-container {

    display:
        grid;

    grid-template-columns:
        1.1fr
        .9fr;

    gap:
        45px;

    align-items:
        center;

}


/* IMAGE GALLERY */

/* CONTENT */

.details-content {

    max-width:
        600px;

}


.category {

    display:
        inline-block;

    padding:
        7px
        13px;

    margin-bottom:
        18px;

    border-radius:
        20px;

    background:
        rgba(181,200,137,.1);

    color:
        #b5c889;

    font-size:
        11px;

    font-weight:
        bold;

    text-transform:
        uppercase;

    letter-spacing:
        1px;

}


.details-content h1 {

    font-size:
        clamp(42px, 5vw, 70px);

    line-height:
        .95;

    margin-bottom:
        20px;

}


.location {

    color:
        #9da49a;

    margin-bottom:
        25px;

}


.description {

    color:
        #b8beb5;

    line-height:
        1.8;

    margin-bottom:
        30px;

}


/* INFO */

.info-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap:
        12px;

    margin-bottom:
        30px;

}


.info-box {

    padding:
        17px;

    border-radius:
        15px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid
        rgba(255,255,255,.08);

}


.info-box small {

    display:
        block;

    color:
        #777;

    font-size:
        10px;

    text-transform:
        uppercase;

    margin-bottom:
        5px;

}


.info-box strong {

    font-size:
        14px;

}


/* PRICE */

.price-box {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        25px;

}


.price-label {

    color:
        #9da49a;

    font-size:
        13px;

}


.price {

    color:
        #b5c889;

    font-size:
        30px;

    font-weight:
        bold;

}


/* BUTTON */

.book-button {

    display:
        block;

    width:
        100%;

    padding:
        17px;

    border-radius:
        30px;

    background:
        #8b9b62;

    color:
        #10120d;

    text-align:
        center;

    font-size:
        16px;

    font-weight:
        bold;

    transition:
        .3s;

}


.book-button:hover {

    transform:
        translateY(-3px);

    background:
        #b5c889;

}


.login-note {

    margin-top:
        12px;

    text-align:
        center;

    color:
        #777;

    font-size:
        11px;

}


/* MOBILE */

@media (max-width: 850px) {

    .details-container {

        grid-template-columns:
            1fr;

    }


    .details-image {

        height:
            400px;

    }

}


@media (max-width: 500px) {

    .details-page {

        padding:
            110px
            5%
            60px;

    }


    .details-image {

        height:
            320px;

    }


    .info-grid {

        grid-template-columns:
            1fr;

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


<!-- DETAILS -->

<main class="details-page">


<a
    href="../index.php"
    class="back-link"
>
    ← Back to Explore
</a>


<div class="details-container">


<!-- IMAGE GALLERY -->
<div class="details-image-gallery">
    <?php if (!empty($gallery_images)): ?>
        <div class="details-main-image-wrap">
            <img
                id="detailsMainImage"
                class="details-main-image"
                src="../assets/images/<?php echo htmlspecialchars($gallery_images[0]); ?>"
                alt="<?php echo htmlspecialchars($adventure["adventure_name"]); ?>"
            >
        </div>
        <?php if (count($gallery_images) > 1): ?>
            <div class="details-thumbs">
                <?php foreach ($gallery_images as $index => $gallery_image): ?>
                    <button type="button" class="details-thumb <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showAdventureImage(this, '<?php echo htmlspecialchars('../assets/images/' . $gallery_image, ENT_QUOTES); ?>')">
                        <img src="../assets/images/<?php echo htmlspecialchars($gallery_image); ?>" alt="Adventure image <?php echo $index + 1; ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="details-main-image-wrap" style="display:grid;place-items:center;color:#777">NO IMAGE</div>
    <?php endif; ?>
</div>


<!-- CONTENT -->

<div class="details-content">


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


<!-- INFO -->

<div class="info-grid">


<div class="info-box">

<small>
    Difficulty
</small>

<strong>

<?php
echo htmlspecialchars(
    $adventure[
        "difficulty_level"
    ]
);
?>

</strong>

</div>


<div class="info-box">

<small>
    Duration
</small>

<strong>

<?php
echo htmlspecialchars(
    $adventure[
        "duration"
    ]
);
?>

</strong>

</div>


<div class="info-box">

<small>
    Capacity
</small>

<strong>

<?php
echo $adventure[
    "capacity"
];
?>

 people

</strong>

</div>


<div class="info-box">

<small>
    Category
</small>

<strong>

<?php
echo htmlspecialchars(
    $adventure[
        "category_name"
    ]
);
?>

</strong>

</div>


</div>


<!-- PRICE -->

<div class="price-box">

<span class="price-label">

    Price per person

</span>


<span class="price">

    Rs.

    <?php
    echo number_format(
        $adventure["price"],
        2
    );
    ?>

</span>

</div>


<!-- BOOK -->

<?php if (
    isset($_SESSION["user_id"])
): ?>


<a
    href="book-adventure.php?id=<?php
        echo $adventure[
            "adventure_id"
        ];
    ?>"
    class="book-button"
>
    Book This Adventure →
</a>


<?php else: ?>


<a
    href="../auth/login.php?redirect=<?php
        echo urlencode(
            "pages/adventure-details.php?id="
            . $adventure_id
        );
    ?>"
    class="book-button"
>
    Login to Book →
</a>


<p class="login-note">

    You need an ExploreX account
    to make a booking.

</p>


<?php endif; ?>


</div>


</div>


</main>


<script>
function showAdventureImage(button, src) {
    const main = document.getElementById('detailsMainImage');
    if (!main) return;
    main.style.opacity = '0.35';
    setTimeout(() => { main.src = src; main.style.opacity = '1'; }, 120);
    document.querySelectorAll('.details-thumb').forEach(el => el.classList.remove('active'));
    button.classList.add('active');
}
</script>

</body>

</html>