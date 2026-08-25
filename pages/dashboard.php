<?php

session_start();

require_once "../config/db.php";


// =====================================
// LOGIN CHECK
// =====================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit();

}


// =====================================
// USER ONLY
// =====================================

if ($_SESSION["role"] !== "USER") {

    header("Location: ../admin/dashboard.php");
    exit();

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
        ExploreX | Dashboard
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        /* =====================================
           DASHBOARD PAGE
        ===================================== */

        .dashboard-page {

            min-height: 100vh;

            padding: 130px 7% 80px;

        }


        .dashboard-container {

            max-width: 1100px;

            margin: 0 auto;

        }


        /* =====================================
           WELCOME SECTION
        ===================================== */

        .dashboard-header {

            margin-bottom: 45px;

        }


        .dashboard-header h1 {

            font-size: clamp(42px, 6vw, 70px);

            line-height: 0.95;

            margin: 10px 0 18px;

        }


        .dashboard-header p {

            color: #9da49a;

            font-size: 15px;

        }


        .welcome-name {

            color: #b5c889;

        }


        /* =====================================
           ACTION CARDS
        ===================================== */

        .dashboard-actions {

            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 22px;

        }


        .dashboard-action {

            display: block;

            padding: 32px;

            border-radius: 25px;

            background: rgba(255,255,255,.05);

            border: 1px solid rgba(255,255,255,.10);

            transition: .25s;

        }


        .dashboard-action:hover {

            transform: translateY(-4px);

            background: rgba(255,255,255,.07);

            border-color:
                rgba(181,200,137,.30);

        }


        .action-icon {

            width: 55px;

            height: 55px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 17px;

            background: rgba(181,200,137,.10);

            font-size: 25px;

            margin-bottom: 20px;

        }


        .dashboard-action h2 {

            font-size: 23px;

            margin-bottom: 10px;

        }


        .dashboard-action p {

            color: #858b83;

            font-size: 13px;

            line-height: 1.7;

        }


        .action-link {

            display: block;

            margin-top: 20px;

            color: #b5c889;

            font-size: 13px;

            font-weight: bold;

        }


        /* =====================================
           BOTTOM SECTION
        ===================================== */

        .dashboard-bottom {

            margin-top: 22px;

            padding: 32px;

            border-radius: 25px;

            background: rgba(255,255,255,.035);

            border: 1px solid rgba(255,255,255,.08);

        }


        .dashboard-bottom h2 {

            font-size: 24px;

            margin-bottom: 10px;

        }


        .dashboard-bottom p {

            color: #858b83;

            font-size: 13px;

            margin-bottom: 22px;

        }


        .explore-button {

            display: inline-block;

            padding: 13px 24px;

            border-radius: 30px;

            background: #8b9b62;

            color: #10120d;

            font-size: 13px;

            font-weight: bold;

        }


        .explore-button:hover {

            background: #b5c889;

        }


        /* =====================================
           MOBILE
        ===================================== */

        @media (max-width: 700px) {

            .dashboard-page {

                padding: 110px 5% 60px;

            }


            .dashboard-actions {

                grid-template-columns: 1fr;

            }


            .dashboard-action {

                padding: 25px;

            }


            .dashboard-bottom {

                padding: 25px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================
     USER NAVIGATION
===================================== -->

<nav class="navbar">


    <a
        href="../index.php"
        class="logo"
    >

        Explore<span>X</span>

    </a>


    <div class="nav-links">


        <!-- HOME -->

        <a href="../index.php">

            Home

        </a>


        <!-- ADVENTURES -->

        <a href="../index.php#adventures">

            Adventures

        </a>


        <!-- MY BOOKINGS -->

        <a href="my-bookings.php">

            My Bookings

        </a>


        <!-- LOGOUT -->

        <a href="../auth/logout.php">

            Logout

        </a>


    </div>

</nav>



<!-- =====================================
     DASHBOARD CONTENT
===================================== -->

<main class="dashboard-page">


    <div class="dashboard-container">


        <!-- =================================
             WELCOME
        ================================== -->

        <div class="dashboard-header">


            <p class="eyebrow">

                MY EXPLOREX

            </p>


            <h1>

                Welcome,

                <span class="welcome-name">

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["name"]
                    );

                    ?>

                </span>

            </h1>


            <p>

                Discover new adventures and
                manage your ExploreX journey.

            </p>


        </div>



        <!-- =================================
             ACTIONS
        ================================== -->

        <div class="dashboard-actions">


            <!-- MY BOOKINGS -->

            <a
                href="my-bookings.php"
                class="dashboard-action"
            >


                <div class="action-icon">

                    📋

                </div>


                <h2>

                    My Bookings

                </h2>


                <p>

                    View and manage all your
                    adventure bookings, participants,
                    booking dates and status.

                </p>


                <span class="action-link">

                    View My Bookings →

                </span>


            </a>



            <!-- ADVENTURES -->

            <a
                href="../index.php#adventures"
                class="dashboard-action"
            >


                <div class="action-icon">

                    🏔️

                </div>


                <h2>

                    Explore Adventures

                </h2>


                <p>

                    Discover exciting adventures,
                    beautiful destinations and
                    unforgettable experiences.

                </p>


                <span class="action-link">

                    Explore Adventures →

                </span>


            </a>


        </div>



        <!-- =================================
             BOTTOM
        ================================== -->

        <div class="dashboard-bottom">


            <h2>

                Ready for your next adventure?

            </h2>


            <p>

                Explore Sri Lanka's exciting
                adventure experiences with ExploreX.

            </p>


            <a
                href="../index.php#adventures"
                class="explore-button"
            >

                Explore Adventures →

            </a>


        </div>


    </div>


</main>


</body>

</html>