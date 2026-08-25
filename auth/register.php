<?php

require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($name) || empty($email) || empty($password)) {
        $message = "Please fill all required fields.";
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } 
    elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } 
    elseif (strlen($password) < 6) {
        $message = "Password must contain at least 6 characters.";
    } 
    else {

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "Email already registered.";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users (name, email, password, phone, role)
                 VALUES (?, ?, ?, ?, 'USER')"
            );

            $stmt->bind_param(
                "ssss",
                $name,
                $email,
                $hashed_password,
                $phone
            );

            if ($stmt->execute()) {

                $message = "Registration successful!";

            } else {

                $message = "Registration failed.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>ExploreX - Register</title>

</head>

<body>

    <h1>ExploreX</h1>

    <h2>Create Account</h2>

    <?php if (!empty($message)): ?>

        <p>
            <?php echo htmlspecialchars($message); ?>
        </p>

    <?php endif; ?>

    <form method="POST">

        <label>Name</label><br>

        <input
            type="text"
            name="name"
            required
        >

        <br><br>

        <label>Email</label><br>

        <input
            type="email"
            name="email"
            required
        >

        <br><br>

        <label>Phone</label><br>

        <input
            type="text"
            name="phone"
        >

        <br><br>

        <label>Password</label><br>

        <input
            type="password"
            name="password"
            required
        >

        <br><br>

        <label>Confirm Password</label><br>

        <input
            type="password"
            name="confirm_password"
            required
        >

        <br><br>

        <button type="submit">
            Create Account
        </button>

    </form>

</body>

</html>