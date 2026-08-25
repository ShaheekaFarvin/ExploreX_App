<?php

session_start();

require_once "config/db.php";

$adventures = [];

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

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $adventures[] = $row;

    }

}

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
        ExploreX | Discover Your Next Adventure
    </title>


    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >
</head>


<body>


<!-- =====================================
     NAVIGATION
===================================== -->

<!-- SHARED EXPLOREX NAVIGATION -->
<?php
$base_path = "";
?>
<?php require __DIR__ . "/includes/navbar.php"; ?>



<!-- =====================================
     HERO SECTION
===================================== -->

<section class="hero">


    <div class="hero-content">


        <p class="eyebrow">

            EXPLORE • EXPERIENCE • REMEMBER

        </p>


        <h1>

            Discover Your

            <span>
                Next Adventure.
            </span>

        </h1>


        <p class="hero-description">

            Explore breathtaking destinations,
            exciting adventures and unforgettable
            experiences across Sri Lanka.

        </p>


        <div class="hero-buttons">


            <a
                href="#adventures"
                class="primary-button"
            >

                Explore Adventures

            </a>


            <a
                href="#about"
                class="secondary-button"
            >

                Learn More

            </a>


        </div>


    </div>


</section>



<!-- =====================================
     CATEGORIES
===================================== -->

<section class="categories">


    <div class="section-heading">


        <p class="eyebrow">

            FIND YOUR EXPERIENCE

        </p>


        <h2>

            Explore by Category

        </h2>


    </div>


    <div class="category-grid">


        <div class="glass-card">

            <div class="category-icon">
                🏔️
            </div>

            <h3>
                Hiking
            </h3>

            <p>
                Discover beautiful mountain trails.
            </p>

        </div>


        <div class="glass-card">

            <div class="category-icon">
                🏕️
            </div>

            <h3>
                Camping
            </h3>

            <p>
                Experience nature under the stars.
            </p>

        </div>


        <div class="glass-card">

            <div class="category-icon">
                🌊
            </div>

            <h3>
                Water Sports
            </h3>

            <p>
                Feel the excitement of the water.
            </p>

        </div>


        <div class="glass-card">

            <div class="category-icon">
                🧗
            </div>

            <h3>
                Rock Climbing
            </h3>

            <p>
                Challenge yourself with new heights.
            </p>

        </div>


    </div>


</section>



<!-- =====================================
     ADVENTURES
===================================== -->

<section
    class="adventures"
    id="adventures"
>


    <div class="section-heading">


        <p class="eyebrow">

            POPULAR DESTINATIONS

        </p>


        <h2>

            Featured Adventures

        </h2>


    </div>


    <div class="adventure-grid">


        <?php if (count($adventures) > 0): ?>


            <?php foreach ($adventures as $adventure): ?>


                <a
                    href="pages/adventure-details.php?id=<?php
                        echo $adventure["adventure_id"];
                    ?>"
                    class="adventure-card"
                >


                    <div
                        class="adventure-image"
                        style="
                            background-image:
                            linear-gradient(
                                to top,
                                rgba(0, 0, 0, 0.8),
                                transparent
                            ),
                            url('assets/images/<?php
                                echo htmlspecialchars(
                                    $adventure["image_url"]
                                );
                            ?>');
                        "
                    >


                        <span>

                            <?php

                            echo htmlspecialchars(
                                $adventure["category_name"]
                            );

                            ?>

                        </span>


                    </div>


                    <div class="adventure-content">


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


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $adventure["adventure_name"]
                            );

                            ?>

                        </h3>


                        <p>

                            <?php

                            echo htmlspecialchars(
                                $adventure["description"]
                            );

                            ?>

                        </p>


                        <div class="adventure-footer">


                            <span>

                                <?php

                                echo htmlspecialchars(
                                    $adventure[
                                        "difficulty_level"
                                    ]
                                );

                                ?>

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


                    </div>


                </a>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="glass-card">


                <h3>

                    No adventures available

                </h3>


                <p>

                    Adventures will appear here once
                    they are added.

                </p>


            </div>


        <?php endif; ?>


    </div>


</section>



<!-- =====================================
     ABOUT
===================================== -->

<section
    class="about"
    id="about"
>


    <div class="about-content">


        <p class="eyebrow">

            ABOUT EXPLOREX

        </p>


        <h2>

            Your Journey
            Starts Here.

        </h2>


        <p>

            ExploreX helps adventure seekers discover
            exciting destinations and unforgettable
            experiences across Sri Lanka.

        </p>


    </div>


</section>



<!-- =====================================
     FOOTER
===================================== -->

<footer>


    <div class="logo">

        Explore<span>X</span>

    </div>


    <p>

        Explore • Experience • Remember

    </p>


    <p>

        © 2026 ExploreX. All rights reserved.

    </p>


</footer>



<script src="assets/js/script.js"></script>


</body>

</html>