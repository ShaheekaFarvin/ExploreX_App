<?php

session_start();
require_once "../config/db.php";

// A logged-in user does not need to register again.
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

$name = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {
        $message = "Please fill in all required fields.";
        $message_type = "error";
    } elseif (strlen($name) < 2 || strlen($name) > 100) {
        $message = "Name must be between 2 and 100 characters.";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } elseif (strlen($email) > 150) {
        $message = "Email address is too long.";
        $message_type = "error";
    } elseif ($phone !== "" && !preg_match('/^[0-9+()\-\s]{7,20}$/', $phone)) {
        $message = "Please enter a valid phone number.";
        $message_type = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } else {

        // Check for an existing account before inserting.
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ? LIMIT 1"
        );

        if (!$check) {
            $message = "Something went wrong. Please try again.";
            $message_type = "error";
        } else {

            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {

                $message = "An account with this email already exists.";
                $message_type = "error";

            } else {

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Every self-registered account is always a normal USER.
                // ADMIN accounts must be created separately by the system owner.
                $stmt = $conn->prepare(
                    "INSERT INTO users (name, email, password, phone, role)
                     VALUES (?, ?, ?, ?, 'USER')"
                );

                if (!$stmt) {
                    $message = "Something went wrong. Please try again.";
                    $message_type = "error";
                } else {

                    $stmt->bind_param(
                        "ssss",
                        $name,
                        $email,
                        $hashed_password,
                        $phone
                    );

                    if ($stmt->execute()) {
                        $stmt->close();
                        $check->close();

                        header("Location: login.php?registered=1");
                        exit();
                    }

                    // Handles any unexpected database failure, including a race
                    // against the UNIQUE email constraint.
                    if ($conn->errno === 1062) {
                        $message = "An account with this email already exists.";
                    } else {
                        $message = "Registration failed. Please try again.";
                    }
                    $message_type = "error";
                    $stmt->close();
                }
            }

            $check->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | ExploreX</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=2">
</head>

<body class="auth-page">

<?php
$base_path = "../";
require __DIR__ . "/../includes/navbar.php";
?>

<main class="auth-wrapper auth-wrapper-register">

    <section class="auth-card">

        <div class="auth-intro">
            <p class="eyebrow">START EXPLORING</p>
            <h1>Create your <span>account.</span></h1>
            <p>Join ExploreX and keep all your Sri Lankan adventures and bookings in one place.</p>
        </div>

        <?php if ($message !== ""): ?>
            <div class="auth-message <?php echo htmlspecialchars($message_type); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form" novalidate>

            <div class="form-group">
                <label for="name">Full name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    placeholder="Your full name"
                    autocomplete="name"
                    maxlength="100"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="you@example.com"
                        autocomplete="email"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Phone <span class="optional">(optional)</span></label>
                    <input
                        id="phone"
                        type="tel"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone); ?>"
                        placeholder="07X XXX XXXX"
                        autocomplete="tel"
                        maxlength="20"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="At least 6 characters"
                        autocomplete="new-password"
                        minlength="6"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input
                        id="confirm_password"
                        type="password"
                        name="confirm_password"
                        placeholder="Repeat your password"
                        autocomplete="new-password"
                        minlength="6"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="auth-submit">
                Create Account <span>→</span>
            </button>

        </form>

        <p class="auth-switch">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </section>

</main>

</body>
</html>
