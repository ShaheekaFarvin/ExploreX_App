<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ExploreX | Discover Your Next Adventure</title>

    <link rel="stylesheet"
          href="assets/css/style.css">

</head>

<body>

    <!-- Navigation -->

    <nav class="navbar">

        <a href="index.php" class="logo">
            Explore<span>X</span>
        </a>

        <div class="nav-links">

            <a href="index.php">Home</a>

            <a href="#adventures">Adventures</a>

            <a href="#about">About</a>

            <?php if (isset($_SESSION["user_id"])): ?>

                <?php if ($_SESSION["role"] === "ADMIN"): ?>

                    <a href="admin/dashboard.php">
                        Dashboard
                    </a>

                <?php else: ?>

                    <a href="pages/dashboard.php">
                        Dashboard
                    </a>

                <?php endif; ?>

                <a href="auth/logout.php">
                    Logout
                </a>

            <?php else: ?>

                <a href="auth/login.php">
                    Login
                </a>

                <a href="auth/register.php"
                   class="nav-button">
                    Get Started
                </a>

            <?php endif; ?>

        </div>

    </nav>


    <!-- Hero Section -->

    <section class="hero">

        <div class="hero-content">

            <p class="eyebrow">
                EXPLORE • EXPERIENCE • REMEMBER
            </p>

            <h1>
                Discover Your
                <span>Next Adventure.</span>
            </h1>

            <p class="hero-description">
                Explore breathtaking destinations,
                exciting adventures and unforgettable
                experiences across Sri Lanka.
            </p>

            <div class="hero-buttons">

                <a href="#adventures"
                   class="primary-button">
                    Explore Adventures
                </a>

                <a href="#about"
                   class="secondary-button">
                    Learn More
                </a>

            </div>

        </div>

    </section>


    <!-- Categories -->

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
                <div class="category-icon">🏔️</div>
                <h3>Hiking</h3>
                <p>Discover beautiful mountain trails.</p>
            </div>

            <div class="glass-card">
                <div class="category-icon">🏕️</div>
                <h3>Camping</h3>
                <p>Experience nature under the stars.</p>
            </div>

            <div class="glass-card">
                <div class="category-icon">🌊</div>
                <h3>Water Sports</h3>
                <p>Feel the excitement of the water.</p>
            </div>

            <div class="glass-card">
                <div class="category-icon">🧗</div>
                <h3>Rock Climbing</h3>
                <p>Challenge yourself with new heights.</p>
            </div>

        </div>

    </section>


    <!-- Adventures -->

    <section class="adventures"
             id="adventures">

        <div class="section-heading">

            <p class="eyebrow">
                POPULAR DESTINATIONS
            </p>

            <h2>
                Featured Adventures
            </h2>

        </div>

        <div class="adventure-grid">

            <div class="adventure-card">

                <div class="adventure-image">
                    <span>Mountain</span>
                </div>

                <div class="adventure-content">

                    <p class="location">
                        📍 Knuckles, Sri Lanka
                    </p>

                    <h3>
                        Knuckles Mountain Trek
                    </h3>

                    <p>
                        Explore breathtaking mountain
                        landscapes and hidden trails.
                    </p>

                    <div class="adventure-footer">

                        <span>
                            ★ 4.8
                        </span>

                        <span>
                            From Rs. 5,000
                        </span>

                    </div>

                </div>

            </div>


            <div class="adventure-card">

                <div class="adventure-image adventure-two">
                    <span>Water</span>
                </div>

                <div class="adventure-content">

                    <p class="location">
                        📍 Kitulgala, Sri Lanka
                    </p>

                    <h3>
                        River Rafting
                    </h3>

                    <p>
                        Experience an exciting adventure
                        through the beautiful river.
                    </p>

                    <div class="adventure-footer">

                        <span>
                            ★ 4.7
                        </span>

                        <span>
                            From Rs. 4,500
                        </span>

                    </div>

                </div>

            </div>


            <div class="adventure-card">

                <div class="adventure-image adventure-three">
                    <span>Hiking</span>
                </div>

                <div class="adventure-content">

                    <p class="location">
                        📍 Ella, Sri Lanka
                    </p>

                    <h3>
                        Ella Mountain Hike
                    </h3>

                    <p>
                        Walk through stunning landscapes
                        and discover hidden views.
                    </p>

                    <div class="adventure-footer">

                        <span>
                            ★ 4.9
                        </span>

                        <span>
                            From Rs. 3,500
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- About -->

    <section class="about"
             id="about">

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


    <!-- Footer -->

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