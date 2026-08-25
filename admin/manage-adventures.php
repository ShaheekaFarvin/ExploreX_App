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


// Delete Adventure

if (
    isset($_GET["delete"]) &&
    is_numeric($_GET["delete"])
) {

    $adventure_id = (int) $_GET["delete"];


    // Delete images first

    $image_stmt = $conn->prepare("
        SELECT image_url
        FROM adventure_images
        WHERE adventure_id = ?
    ");

    $image_stmt->bind_param(
        "i",
        $adventure_id
    );

    $image_stmt->execute();

    $images = $image_stmt->get_result();


    while ($image = $images->fetch_assoc()) {

        $image_path =
            "../assets/images/"
            . $image["image_url"];

        if (
            file_exists($image_path)
        ) {
            unlink($image_path);
        }
    }

    $image_stmt->close();


    // Delete adventure

    $delete_stmt = $conn->prepare("
        DELETE FROM adventures
        WHERE adventure_id = ?
    ");

    $delete_stmt->bind_param(
        "i",
        $adventure_id
    );

    $delete_stmt->execute();

    $delete_stmt->close();


    header(
        "Location: manage-adventures.php?deleted=1"
    );

    exit();
}


// Get Adventures

$sql = "
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

    ORDER BY a.created_at DESC
";

$adventures = $conn->query($sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Manage Adventures | ExploreX
</title>

<link rel="stylesheet"
      href="../assets/css/style.css">


<style>

.manage-page {
    padding: 130px 7% 80px;
}

.manage-header {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 40px;
}

.manage-header h1 {
    font-size: 55px;

    line-height: 1;
}

.manage-header p {
    color: #9da49a;

    margin-top: 10px;
}

.add-button {
    display: inline-block;

    padding: 13px 22px;

    border-radius: 25px;

    background: #8b9b62;

    color: #10120d;

    font-weight: bold;

    white-space: nowrap;
}


.adventure-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 22px;
}


.adventure-card {

    overflow: hidden;

    border-radius: 23px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid
        rgba(255,255,255,.1);

    backdrop-filter: blur(20px);
}


.adventure-image {

    height: 230px;

    background-position: center;

    background-size: cover;

    background-repeat: no-repeat;

    position: relative;
}


.adventure-category {

    position: absolute;

    top: 15px;

    left: 15px;

    padding: 7px 12px;

    border-radius: 20px;

    background:
        rgba(0,0,0,.55);

    color: #b5c889;

    font-size: 10px;

    font-weight: bold;

    text-transform: uppercase;
}


.adventure-content {

    padding: 22px;
}


.adventure-content h3 {

    font-size: 22px;

    margin-bottom: 8px;
}


.location {

    color: #9da49a;

    font-size: 13px;

    margin-bottom: 15px;
}


.description {

    color: #9da49a;

    font-size: 13px;

    line-height: 1.6;

    margin-bottom: 18px;

    display: -webkit-box;

    -webkit-line-clamp: 3;

    -webkit-box-orient: vertical;

    overflow: hidden;
}


.info-row {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 8px;

    margin-bottom: 20px;
}


.info-box {

    padding: 10px;

    border-radius: 10px;

    background:
        rgba(255,255,255,.04);
}


.info-box small {

    display: block;

    color: #777;

    font-size: 10px;

    margin-bottom: 3px;
}


.info-box strong {

    font-size: 12px;
}


.card-footer {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    padding-top: 15px;

    border-top:
        1px solid
        rgba(255,255,255,.08);
}


.price {

    color: #b5c889;

    font-weight: bold;

}


.actions {

    display: flex;

    gap: 8px;
}


.edit-button,
.delete-button {

    padding: 8px 13px;

    border-radius: 18px;

    font-size: 11px;

    font-weight: bold;
}


.edit-button {

    color: #b5c889;

    border:
        1px solid
        rgba(181,200,137,.25);
}


.delete-button {

    color: #e99b9b;

    border:
        1px solid
        rgba(233,155,155,.25);

    background: transparent;

    cursor: pointer;
}


.empty-state {

    padding: 60px;

    text-align: center;

    border-radius: 25px;

    background:
        rgba(255,255,255,.04);

    border:
        1px solid
        rgba(255,255,255,.1);
}


@media (max-width: 1000px) {

    .adventure-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 650px) {

    .manage-header {

        flex-direction: column;

        align-items: flex-start;

    }

    .adventure-grid {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<!-- SHARED EXPLOREX NAVIGATION -->
<?php
$base_path = "../";
?>
<?php require __DIR__ . "/../includes/navbar.php"; ?>

<div class="admin-back-wrap"><a class="admin-back-link" href="dashboard.php">← BACK TO DASHBOARD</a></div>


<main class="manage-page">


<div class="manage-header">

    <div>

        <p class="eyebrow">
            ADMIN MANAGEMENT
        </p>

        <h1>
            Adventures
        </h1>

        <p>
            Add, edit and manage your adventures.
        </p>

    </div>


    <a
        href="add-adventure.php"
        class="add-button"
    >
        + Add Adventure
    </a>

</div>


<?php if (
    isset($_GET["deleted"])
): ?>

<div style="
    padding:13px;
    margin-bottom:25px;
    border-radius:12px;
    background:rgba(169,213,143,.1);
    color:#a9d58f;
">

    Adventure deleted successfully.

</div>

<?php endif; ?>


<?php if (
    $adventures->num_rows > 0
): ?>


<div class="adventure-grid">


<?php while (
    $adventure =
    $adventures->fetch_assoc()
): ?>


<div class="adventure-card">


<div
    class="adventure-image"

    style="background-image:
        linear-gradient(
            to top,
            rgba(0,0,0,.75),
            transparent
        ),
        url('../assets/images/<?php
            echo htmlspecialchars(
                $adventure["image_url"]
            );
        ?>');"
>


<span class="adventure-category">

    <?php
    echo htmlspecialchars(
        $adventure["category_name"]
    );
    ?>

</span>


</div>


<div class="adventure-content">


<h3>

    <?php
    echo htmlspecialchars(
        $adventure["adventure_name"]
    );
    ?>

</h3>


<p class="location">

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


<p class="description">

    <?php
    echo htmlspecialchars(
        $adventure["description"]
    );
    ?>

</p>


<div class="info-row">


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

    </strong>

</div>


<div class="info-box">

    <small>
        Price
    </small>

    <strong>

        Rs.

        <?php
        echo number_format(
            $adventure["price"],
            2
        );
        ?>

    </strong>

</div>


</div>


<div class="card-footer">


<span class="price">

    Rs.

    <?php
    echo number_format(
        $adventure["price"],
        2
    );
    ?>

</span>


<div class="actions">


<a
    href="edit-adventure.php?id=<?php
        echo $adventure[
            "adventure_id"
        ];
    ?>"
    class="edit-button"
>
    Edit
</a>


<a
    href="manage-adventures.php?delete=<?php
        echo $adventure[
            "adventure_id"
        ];
    ?>"
    class="delete-button"

    onclick="
        return confirm(
            'Are you sure you want to delete this adventure?'
        );
    "
>
    Delete
</a>


</div>


</div>


</div>


</div>


<?php endwhile; ?>


</div>


<?php else: ?>


<div class="empty-state">

    <h2>
        No Adventures Found
    </h2>

    <p style="color:#9da49a;">
        Start by adding your first adventure.
    </p>

</div>


<?php endif; ?>


</main>

</body>

</html>