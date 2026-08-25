<?php

session_start();

require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password, role
             FROM users
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["role"] = $user["role"];


                // =================================
                // ADMIN LOGIN
                // =================================

                if ($user["role"] === "ADMIN") {

                    header(
                        "Location: ../admin/dashboard.php"
                    );

                    exit();

                }


                // =================================
                // NORMAL USER LOGIN
                // =================================

                else {

                    header(
                        "Location: ../index.php"
                    );

                    exit();

                }

            } else {

                $message = "Invalid email or password.";

            }

        } else {

            $message = "Invalid email or password.";

        }

        $stmt->close();
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
        ExploreX - Login
    </title>

</head>


<body>


    <h1>
        ExploreX
    </h1>


    <h2>
        Login
    </h2>


    <?php if (!empty($message)): ?>

        <p>

            <?php

            echo htmlspecialchars($message);

            ?>

        </p>

    <?php endif; ?>


    <form method="POST">


        <label>
            Email
        </label>

        <br>


        <input
            type="email"
            name="email"
            required
        >


        <br>
        <br>


        <label>
            Password
        </label>

        <br>


        <input
            type="password"
            name="password"
            required
        >


        <br>
        <br>


        <button type="submit">

            Login

        </button>


    </form>


    <br>


    <a href="register.php">

        Don't have an account?
        Register

    </a>


</body>

</html>