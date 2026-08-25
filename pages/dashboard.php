<?php

session_start();

// The normal user no longer has a separate dashboard.
// Keep this old URL working by sending users to My Bookings.

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if (isset($_SESSION["role"]) && $_SESSION["role"] === "ADMIN") {
    header("Location: ../admin/dashboard.php");
    exit();
}

header("Location: my-bookings.php");
exit();
