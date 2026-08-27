<?php

session_start();
require_once "../config/db.php";

// If already logged in, do not show the login page again.
if (isset($_SESSION["user_id"], $_SESSION["role"])) {
    if ($_SESSION["role"] === "ADMIN") {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../index.php");
    }
    exit();
}

$message = "";
$message_type = "";
$email = "";

// Registration redirects here with ?registered=1.
if (isset($_GET["registered"]) && $_GET["registered"] === "1") {
    $message = "Account created successfully. Please log in.";
    $message_type = "success";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $message = "Please enter your email and password.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password, role, profile_photo
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (password_verify($password, $user["password"])) {

                    // Regenerate the session ID after successful authentication.
                    session_regenerate_id(true);

                    $_SESSION["user_id"] = (int) $user["user_id"];
                    $_SESSION["name"] = $user["name"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["role"] = $user["role"];
                    $_SESSION["profile_photo"] = $user["profile_photo"] ?? "";

                    // Keep the two user types completely separate.
                    if ($user["role"] === "ADMIN") {
                        header("Location: ../admin/dashboard.php");
                    } else {
                        // Normal users have no separate dashboard.
                        header("Location: ../index.php");
                    }
                    exit();

                } else {
                    $message = "Invalid email or password.";
                    $message_type = "error";
                }

            } else {
                $message = "Invalid email or password.";
                $message_type = "error";
            }

            $stmt->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ExploreX</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>

<body class="auth-page">

<?php
$base_path = "../";
require __DIR__ . "/../includes/navbar.php";
?>

<main class="auth-wrapper">

    <section class="auth-card">

        <div class="auth-intro">
            <p class="eyebrow">WELCOME BACK</p>
            <h1>Continue your <span>adventure.</span></h1>
            <p>Log in to manage your ExploreX bookings and discover your next experience.</p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="auth-message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" novalidate>

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    placeholder="you@example.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="form-group">
                <div class="form-label-row">
                    <label for="password">Password</label>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="auth-submit">
                Login to ExploreX <span>→</span>
            </button>

        </form>

        <p class="auth-switch">
            Don't have an account?
            <a href="register.php">Create one</a>
        </p>

    </section>

</main>

</body>
</html>
