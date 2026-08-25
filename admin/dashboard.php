<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "ADMIN") {
    header("Location: ../pages/dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ExploreX - Admin Dashboard</title>

</head>

<body>

    <h1>ExploreX Admin Dashboard</h1>

    <h2>
        Welcome,
        <?php echo htmlspecialchars($_SESSION["name"]); ?>!
    </h2>

    <p>
        You are logged in as an Administrator.
    </p>

    <a href="../auth/logout.php">
        Logout
    </a>

</body>

</html>