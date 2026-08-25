<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "ADMIN") {
    header("Location: ../pages/dashboard.php");
    exit();
}


if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage-adventures.php");
    exit();
}

$adventure_id = (int) $_GET["id"];

$message = "";
$message_type = "";


// Get Categories

$categories = $conn->query("
    SELECT category_id, category_name
    FROM categories
    ORDER BY category_name
");


// Get Locations

$locations = $conn->query("
    SELECT location_id, location_name, district
    FROM locations
    ORDER BY location_name
");


// Get Adventure

$stmt = $conn->prepare("
    SELECT
        a.*,
        ai.image_url

    FROM adventures a

    LEFT JOIN adventure_images ai
        ON a.adventure_id = ai.adventure_id
        AND ai.is_main = TRUE

    WHERE a.adventure_id = ?
");

$stmt->bind_param(
    "i",
    $adventure_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: manage-adventures.php");
    exit();
}

$adventure = $result->fetch_assoc();

$stmt->close();


// Update Adventure

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $adventure_name =
        trim($_POST["adventure_name"]);

    $description =
        trim($_POST["description"]);

    $category_id =
        (int) $_POST["category_id"];

    $location_id =
        (int) $_POST["location_id"];

    $price =
        (float) $_POST["price"];

    $difficulty_level =
        trim($_POST["difficulty_level"]);

    $duration =
        trim($_POST["duration"]);

    $capacity =
        (int) $_POST["capacity"];


    if (
        empty($adventure_name) ||
        empty($description) ||
        $category_id <= 0 ||
        $location_id <= 0 ||
        $price <= 0 ||
        empty($difficulty_level) ||
        empty($duration) ||
        $capacity <= 0
    ) {

        $message =
            "Please fill all fields.";

        $message_type =
            "error";

    } else {


        // Update main data

        $update = $conn->prepare("
            UPDATE adventures

            SET
                category_id = ?,
                location_id = ?,
                adventure_name = ?,
                description = ?,
                price = ?,
                difficulty_level = ?,
                duration = ?,
                capacity = ?

            WHERE adventure_id = ?
        ");

        $update->bind_param(
            "iissdssii",
            $category_id,
            $location_id,
            $adventure_name,
            $description,
            $price,
            $difficulty_level,
            $duration,
            $capacity,
            $adventure_id
        );


        if ($update->execute()) {

            $update->close();


            // Check if new image selected

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"] === UPLOAD_ERR_OK
            ) {

                $image =
                    $_FILES["image"];


                $allowed_types = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];


                if (
                    !in_array(
                        $image["type"],
                        $allowed_types
                    )
                ) {

                    $message =
                        "Adventure updated, but image type is invalid.";

                    $message_type =
                        "error";

                } elseif (
                    $image["size"] >
                    5 * 1024 * 1024
                ) {

                    $message =
                        "Adventure updated, but image is larger than 5MB.";

                    $message_type =
                        "error";

                } else {


                    $extension =
                        strtolower(
                            pathinfo(
                                $image["name"],
                                PATHINFO_EXTENSION
                            )
                        );


                    $new_filename =
                        uniqid(
                            "adventure_",
                            true
                        )
                        . "."
                        . $extension;


                    $upload_path =
                        "../assets/images/"
                        . $new_filename;


                    if (
                        move_uploaded_file(
                            $image["tmp_name"],
                            $upload_path
                        )
                    ) {


                        // Get old image

                        $old_stmt =
                            $conn->prepare("
                                SELECT image_url
                                FROM adventure_images
                                WHERE adventure_id = ?
                                AND is_main = TRUE
                                LIMIT 1
                            ");

                        $old_stmt->bind_param(
                            "i",
                            $adventure_id
                        );

                        $old_stmt->execute();

                        $old_result =
                            $old_stmt->get_result();

                        $old_image = null;

                        if (
                            $old_result->num_rows === 1
                        ) {

                            $old_image =
                                $old_result
                                ->fetch_assoc()
                                ["image_url"];
                        }

                        $old_stmt->close();


                        // Update image

                        $image_update =
                            $conn->prepare("
                                UPDATE adventure_images

                                SET image_url = ?

                                WHERE adventure_id = ?

                                AND is_main = TRUE
                            ");

                        $image_update->bind_param(
                            "si",
                            $new_filename,
                            $adventure_id
                        );

                        $image_update->execute();

                        $image_update->close();


                        // Delete old file

                        if (
                            $old_image &&
                            file_exists(
                                "../assets/images/"
                                . $old_image
                            )
                        ) {

                            unlink(
                                "../assets/images/"
                                . $old_image
                            );
                        }

                    } else {

                        $message =
                            "Adventure updated, but image upload failed.";

                        $message_type =
                            "error";
                    }
                }

            }


            if (empty($message)) {

                $message =
                    "Adventure updated successfully.";

                $message_type =
                    "success";
            }


            // Refresh data

            $stmt = $conn->prepare("
                SELECT
                    a.*,
                    ai.image_url

                FROM adventures a

                LEFT JOIN adventure_images ai
                    ON a.adventure_id =
                       ai.adventure_id
                    AND ai.is_main = TRUE

                WHERE a.adventure_id = ?
            ");

            $stmt->bind_param(
                "i",
                $adventure_id
            );

            $stmt->execute();

            $adventure =
                $stmt->get_result()
                ->fetch_assoc();

            $stmt->close();


        } else {

            $message =
                "Failed to update adventure.";

            $message_type =
                "error";

            $update->close();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Edit Adventure | ExploreX
</title>

<link rel="stylesheet"
      href="../assets/css/style.css">


<style>

.edit-page {
    padding: 130px 7% 80px;
}

.edit-container {
    max-width: 850px;
    margin: auto;
}

.edit-header {
    margin-bottom: 35px;
}

.edit-header h1 {
    font-size: 55px;
    line-height: 1;
}

.edit-header p {
    color: #9da49a;
    margin-top: 10px;
}

.back-link {
    display: inline-block;
    margin-bottom: 25px;
    color: #b5c889;
}

.edit-form {
    padding: 35px;

    background:
        rgba(255,255,255,.05);

    border:
        1px solid
        rgba(255,255,255,.1);

    border-radius: 25px;

    backdrop-filter: blur(20px);
}

.form-grid {
    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    color: #d8ddd3;

    font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    padding: 14px 16px;

    border-radius: 12px;

    border:
        1px solid
        rgba(255,255,255,.12);

    background:
        rgba(255,255,255,.06);

    color: white;

    outline: none;

    font-family: inherit;
}

.form-group select option {
    background: #101410;
}

.form-group textarea {
    min-height: 150px;
    resize: vertical;
}

.current-image {
    width: 100%;

    height: 250px;

    object-fit: cover;

    border-radius: 15px;

    margin-bottom: 15px;
}

.image-preview {
    width: 100%;

    height: 250px;

    object-fit: cover;

    border-radius: 15px;

    margin-top: 15px;

    display: none;
}

.submit-button {
    width: 100%;

    border: none;

    padding: 16px;

    border-radius: 30px;

    background: #8b9b62;

    color: #10120d;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

.message {
    padding: 13px;

    border-radius: 12px;

    margin-bottom: 20px;
}

.message.success {
    background:
        rgba(169,213,143,.1);

    color: #a9d58f;
}

.message.error {
    background:
        rgba(233,155,155,.1);

    color: #e99b9b;
}

@media (max-width: 700px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .full-width {
        grid-column: auto;
    }

    .edit-form {
        padding: 22px;
    }

    .edit-header h1 {
        font-size: 42px;
    }

}

</style>

</head>


<body>


<nav class="navbar">

    <a
        href="../index.php"
        class="logo"
    >
        Explore<span>X</span>
    </a>


    <div class="nav-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="manage-adventures.php">
            Adventures
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

</nav>


<main class="edit-page">


<div class="edit-container">


<a
    href="manage-adventures.php"
    class="back-link"
>
    ← Back to Adventures
</a>


<div class="edit-header">

    <p class="eyebrow">
        ADMIN MANAGEMENT
    </p>

    <h1>
        Edit Adventure
    </h1>

    <p>
        Update adventure information and image.
    </p>

</div>


<div class="edit-form">


<?php if (!empty($message)): ?>

<div class="message <?php
    echo $message_type;
?>">

    <?php
    echo htmlspecialchars($message);
    ?>

</div>

<?php endif; ?>


<form
    method="POST"
    enctype="multipart/form-data"
>


<div class="form-grid">


<div class="form-group full-width">

<label>
    Adventure Name
</label>

<input
    type="text"
    name="adventure_name"
    value="<?php
        echo htmlspecialchars(
            $adventure["adventure_name"]
        );
    ?>"
    required
>

</div>


<div class="form-group">

<label>
    Category
</label>

<select
    name="category_id"
    required
>

<option value="">
    Select Category
</option>

<?php while (
    $category =
    $categories->fetch_assoc()
): ?>

<option
    value="<?php
        echo $category["category_id"];
    ?>"

    <?php
    if (
        $category["category_id"]
        ==
        $adventure["category_id"]
    ) {
        echo "selected";
    }
    ?>
>

<?php
echo htmlspecialchars(
    $category["category_name"]
);
?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>
    Location
</label>

<select
    name="location_id"
    required
>

<option value="">
    Select Location
</option>

<?php while (
    $location =
    $locations->fetch_assoc()
): ?>

<option
    value="<?php
        echo $location["location_id"];
    ?>"

    <?php
    if (
        $location["location_id"]
        ==
        $adventure["location_id"]
    ) {
        echo "selected";
    }
    ?>
>

<?php

echo htmlspecialchars(
    $location["location_name"]
);

echo " - ";

echo htmlspecialchars(
    $location["district"]
);

?>

</option>

<?php endwhile; ?>

</select>

</div>


<div class="form-group">

<label>
    Price per Person (Rs.)
</label>

<input
    type="number"
    name="price"
    min="1"
    step="0.01"
    value="<?php
        echo htmlspecialchars(
            $adventure["price"]
        );
    ?>"
    required
>

</div>


<div class="form-group">

<label>
    Maximum Capacity
</label>

<input
    type="number"
    name="capacity"
    min="1"
    value="<?php
        echo htmlspecialchars(
            $adventure["capacity"]
        );
    ?>"
    required
>

</div>


<div class="form-group">

<label>
    Difficulty Level
</label>

<select
    name="difficulty_level"
    required
>

<option value="EASY"
    <?php
    if (
        $adventure["difficulty_level"]
        === "EASY"
    ) {
        echo "selected";
    }
    ?>
>
    Easy
</option>

<option value="MEDIUM"
    <?php
    if (
        $adventure["difficulty_level"]
        === "MEDIUM"
    ) {
        echo "selected";
    }
    ?>
>
    Medium
</option>

<option value="HARD"
    <?php
    if (
        $adventure["difficulty_level"]
        === "HARD"
    ) {
        echo "selected";
    }
    ?>
>
    Hard
</option>

</select>

</div>


<div class="form-group">

<label>
    Duration
</label>

<input
    type="text"
    name="duration"
    value="<?php
        echo htmlspecialchars(
            $adventure["duration"]
        );
    ?>"
    required
>

</div>


<div class="form-group full-width">

<label>
    Description
</label>

<textarea
    name="description"
    required
><?php
echo htmlspecialchars(
    $adventure["description"]
);
?></textarea>

</div>


<div class="form-group full-width">

<label>
    Current Image
</label>

<?php if (
    !empty($adventure["image_url"])
): ?>

<img
    src="../assets/images/<?php
        echo htmlspecialchars(
            $adventure["image_url"]
        );
    ?>"
    class="current-image"
    alt="Current Adventure Image"
>

<?php endif; ?>


<label>
    Replace Image (Optional)
</label>

<input
    type="file"
    name="image"
    id="image"
    accept=".jpg,.jpeg,.png,.webp"
>


<img
    id="imagePreview"
    class="image-preview"
    alt="New Image Preview"
>

</div>


</div>


<button
    type="submit"
    class="submit-button"
>
    Save Changes →
</button>


</form>


</div>


</div>

</main>


<script>

const imageInput =
    document.getElementById("image");

const imagePreview =
    document.getElementById("imagePreview");


imageInput.addEventListener(
    "change",
    function () {

        const file =
            this.files[0];

        if (file) {

            imagePreview.src =
                URL.createObjectURL(file);

            imagePreview.style.display =
                "block";

        }

    }
);

</script>


</body>

</html>