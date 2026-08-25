<?php

session_start();

require_once "../config/db.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../index.php");
    exit();
}

$adventure_id = (int) $_GET["id"];

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

    WHERE a.adventure_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adventure_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: ../index.php");
    exit();
}

$adventure = $result->fetch_assoc();

$stmt->close();


// Get reviews

$review_sql = "
    SELECT
        r.rating,
        r.comment,
        r.review_date,
        u.name
    FROM reviews r
    INNER JOIN users u
        ON r.user_id = u.user_id
    WHERE r.adventure_id = ?
    ORDER BY r.review_date DESC
";

$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $adventure_id);
$review_stmt->execute();

$reviews = $review_stmt->get_result();


// Calculate average rating

$rating_sql = "
    SELECT
        AVG(rating) AS average_rating,
        COUNT(*) AS total_reviews
    FROM reviews
    WHERE adventure_id = ?
";

$rating_stmt = $conn->prepare($rating_sql);
$rating_stmt->bind_param("i", $adventure_id);
$rating_stmt->execute();

$rating_result = $rating_stmt->get_result();
$rating_data = $rating_result->fetch_assoc();

$average_rating = $rating_data["average_rating"]
    ? number_format($rating_data["average_rating"], 1)
    : "0.0";

$total_reviews = $rating_data["total_reviews"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($adventure["adventure_name"]); ?>
        | ExploreX
    </title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        .details-page {
            padding: 130px 7% 80px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #b5c889;
        }

        .details-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .details-image {
            min-height: 550px;
            border-radius: 30px;
            background:
                linear-gradient(
                    to top,
                    rgba(0,0,0,.8),
                    transparent 55%
                ),
                url("../assets/images/<?php
                    echo htmlspecialchars(
                        $adventure["image_url"]
                    );
                ?>") center/cover no-repeat;
            border: 1px solid rgba(255,255,255,.1);
        }

        .details-content {
            padding: 20px 0;
        }

        .details-category {
            color: #b5c889;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 12px;
            font-weight: bold;
        }

        .details-content h1 {
            font-size: clamp(40px, 5vw, 70px);
            line-height: 1;
            margin: 15px 0 25px;
        }

        .details-location {
            color: #9da49a;
            margin-bottom: 25px;
        }

        .details-description {
            color: #9da49a;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .details-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 30px;
        }

        .info-box {
            padding: 20px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 18px;
        }

        .info-box small {
            display: block;
            color: #9da49a;
            margin-bottom: 5px;
        }

        .info-box strong {
            color: #f1f3eb;
        }

        .price {
            font-size: 28px;
            color: #b5c889;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .book-button {
            display: block;
            text-align: center;
            padding: 16px;
            border-radius: 30px;
            background: #8b9b62;
            color: #10120d;
            font-weight: bold;
        }

        .reviews-section {
            margin-top: 80px;
        }

        .reviews-section h2 {
            font-size: 40px;
            margin-bottom: 25px;
        }

        .review-card {
            padding: 20px;
            margin-bottom: 15px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 18px;
        }

        .review-name {
            font-weight: bold;
        }

        .review-rating {
            color: #b5c889;
            margin: 5px 0;
        }

        .review-date {
            color: #777;
            font-size: 12px;
        }

        .review-comment {
            color: #9da49a;
            margin-top: 10px;
        }

        @media (max-width: 800px) {

            .details-container {
                grid-template-columns: 1fr;
            }

            .details-image {
                min-height: 400px;
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

        <?php if (isset($_SESSION["user_id"])): ?>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="../auth/logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="../auth/login.php">
                Login
            </a>

            <a href="../auth/register.php"
               class="nav-button">
                Get Started
            </a>

        <?php endif; ?>

    </div>

</nav>


<main class="details-page">

    <a href="../index.php"
       class="back-link">
        ← Back to Adventures
    </a>


    <div class="details-container">


        <div class="details-image"></div>


        <div class="details-content">

            <p class="details-category">

                <?php
                echo htmlspecialchars(
                    $adventure["category_name"]
                );
                ?>

            </p>


            <h1>

                <?php
                echo htmlspecialchars(
                    $adventure["adventure_name"]
                );
                ?>

            </h1>


            <p class="details-location">

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


            <p class="details-description">

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $adventure["description"]
                    )
                );
                ?>

            </p>


            <div class="details-info">

                <div class="info-box">

                    <small>
                        Difficulty
                    </small>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $adventure["difficulty_level"]
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
                            $adventure["duration"]
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
                        echo htmlspecialchars(
                            $adventure["capacity"]
                        );
                        ?>
                        People
                    </strong>

                </div>


                <div class="info-box">

                    <small>
                        Rating
                    </small>

                    <strong>
                        ★ <?php echo $average_rating; ?>

                        (<?php echo $total_reviews; ?>)
                    </strong>

                </div>

            </div>


            <div class="price">

                Rs.
                <?php
                echo number_format(
                    $adventure["price"],
                    2
                );
                ?>

                <small>
                    / person
                </small>

            </div>


            <?php if (isset($_SESSION["user_id"])): ?>

                <a
                    href="booking.php?id=<?php
                        echo $adventure["adventure_id"];
                    ?>"
                    class="book-button"
                >
                    Book This Adventure →
                </a>

            <?php else: ?>

                <a
                    href="../auth/login.php"
                    class="book-button"
                >
                    Login to Book →
                </a>

            <?php endif; ?>

        </div>

    </div>


    <section class="reviews-section">

        <h2>
            Reviews
        </h2>


        <?php if ($reviews->num_rows > 0): ?>

            <?php while ($review = $reviews->fetch_assoc()): ?>

                <div class="review-card">

                    <div class="review-name">

                        <?php
                        echo htmlspecialchars(
                            $review["name"]
                        );
                        ?>

                    </div>


                    <div class="review-rating">

                        <?php
                        echo str_repeat(
                            "★",
                            (int)$review["rating"]
                        );
                        ?>

                    </div>


                    <div class="review-date">

                        <?php
                        echo htmlspecialchars(
                            $review["review_date"]
                        );
                        ?>

                    </div>


                    <div class="review-comment">

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $review["comment"]
                            )
                        );
                        ?>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p style="color:#9da49a;">
                No reviews yet. Be the first to review
                this adventure!
            </p>

        <?php endif; ?>

    </section>

</main>

</body>

</html>