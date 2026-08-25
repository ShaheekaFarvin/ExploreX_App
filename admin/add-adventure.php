<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION["role"] !== "ADMIN") {
    header("Location: ../index.php");
    exit();
}

$message = "";
$message_type = "";


// Categories

$categories = $conn->query("
    SELECT category_id, category_name
    FROM categories
    ORDER BY category_name
");


// Locations

$locations = $conn->query("
    SELECT location_id, location_name, district
    FROM locations
    ORDER BY location_name
");


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $adventure_name = trim($_POST["adventure_name"]);
    $description = trim($_POST["description"]);
    $category_id = (int) $_POST["category_id"];
    $location_id = (int) $_POST["location_id"];
    $price = (float) $_POST["price"];
    $difficulty_level = trim($_POST["difficulty_level"]);
    $duration = trim($_POST["duration"]);
    $capacity = (int) $_POST["capacity"];


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

        $message = "Please fill all fields.";
        $message_type = "error";

    } elseif (!isset($_FILES["image"])) {

        $message = "Please select an image.";
        $message_type = "error";

    } else {

        $image = $_FILES["image"];

        $allowed_types = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        if (!in_array($image["type"], $allowed_types)) {

            $message =
                "Only JPG, PNG and WEBP images are allowed.";

            $message_type = "error";

        } elseif ($image["size"] > 5 * 1024 * 1024) {

            $message =
                "Image size must be less than 5MB.";

            $message_type = "error";

        } else {

            $extension =
                strtolower(
                    pathinfo(
                        $image["name"],
                        PATHINFO_EXTENSION
                    )
                );

            $new_filename =
                uniqid("adventure_", true)
                . "."
                . $extension;


            $upload_directory =
                "../assets/images/";

            $upload_path =
                $upload_directory
                . $new_filename;


            if (move_uploaded_file(
                $image["tmp_name"],
                $upload_path
            )) {


                // Insert adventure

                $stmt = $conn->prepare("
                    INSERT INTO adventures
                    (
                        category_id,
                        location_id,
                        adventure_name,
                        description,
                        price,
                        difficulty_level,
                        duration,
                        capacity
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");


                $stmt->bind_param(
                    "iissdssi",
                    $category_id,
                    $location_id,
                    $adventure_name,
                    $description,
                    $price,
                    $difficulty_level,
                    $duration,
                    $capacity
                );


                if ($stmt->execute()) {

                    $adventure_id =
                        $stmt->insert_id;


                    // Insert image

                    $image_stmt = $conn->prepare("
                        INSERT INTO adventure_images
                        (
                            adventure_id,
                            image_url,
                            is_main
                        )
                        VALUES (?, ?, TRUE)
                    ");


                    $image_stmt->bind_param(
                        "is",
                        $adventure_id,
                        $new_filename
                    );


                    if ($image_stmt->execute()) {

                        $message =
                            "Adventure added successfully!";

                        $message_type = "success";

                    } else {

                        $message =
                            "Adventure added, but image could not be saved.";

                        $message_type = "error";
                    }


                    $image_stmt->close();

                } else {

                    unlink($upload_path);

                    $message =
                        "Failed to add adventure.";

                    $message_type = "error";
                }


                $stmt->close();

            } else {

                $message =
                    "Failed to upload image.";

                $message_type = "error";
            }
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
    Add Adventure | ExploreX
</title>

<link rel="stylesheet"
      href="../assets/css/style.css">

<style>

.add-page {
    padding: 130px 7% 80px;
}

.add-container {
    max-width: 850px;
    margin: auto;
}

.add-header {
    margin-bottom: 35px;
}

.add-header h1 {
    font-size: 55px;
    line-height: 1;
}

.add-header p {
    color: #9da49a;
    margin-top: 10px;
}

.add-form {
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
    grid-template-columns: 1fr 1fr;
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
    min-height: 140px;
    resize: vertical;
}

.image-preview {
    margin-top: 12px;

    width: 100%;
    height: 220px;

    border-radius: 15px;

    object-fit: cover;

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
    background: rgba(169,213,143,.1);
    color: #a9d58f;
}

.message.error {
    background: rgba(233,155,155,.1);
    color: #e99b9b;
}

.back-link {
    display: inline-block;

    margin-bottom: 25px;

    color: #b5c889;
}

@media (max-width: 700px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .full-width {
        grid-column: auto;
    }

    .add-form {
        padding: 22px;
    }

    .add-header h1 {
        font-size: 42px;
    }

}

</style>

</head>


<body>


<!-- SHARED EXPLOREX NAVIGATION -->
<?php
$base_path = "../";
?>
<?php require __DIR__ . "/../includes/navbar.php"; ?>

<div class="admin-back-wrap"><a class="admin-back-link" href="dashboard.php">← BACK TO DASHBOARD</a></div>


<main class="add-page">

<div class="add-container">


<a
    href="dashboard.php"
    class="back-link"
>
    ← Back to Dashboard
</a>


<div class="add-header">

    <p class="eyebrow">
        ADMIN MANAGEMENT
    </p>

    <h1>
        Add Adventure
    </h1>

    <p>
        Create a new adventure for ExploreX.
    </p>

</div>


<div class="add-form">


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


<!-- Adventure Name -->

<div class="form-group full-width">

<label>
    Adventure Name
</label>

<input
    type="text"
    name="adventure_name"
    placeholder="e.g. Knuckles Mountain Trek"
    required
>

</div>


<!-- Category -->

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


<!-- Location -->

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


<!-- Price -->

<div class="form-group">

<label>
    Price per Person (Rs.)
</label>

<input
    type="number"
    name="price"
    min="1"
    step="0.01"
    placeholder="5000"
    required
>

</div>


<!-- Capacity -->

<div class="form-group">

<label>
    Maximum Capacity
</label>

<input
    type="number"
    name="capacity"
    min="1"
    placeholder="15"
    required
>

</div>


<!-- Difficulty -->

<div class="form-group">

<label>
    Difficulty Level
</label>

<select
    name="difficulty_level"
    required
>

<option value="">
    Select Difficulty
</option>

<option value="EASY">
    Easy
</option>

<option value="MEDIUM">
    Medium
</option>

<option value="HARD">
    Hard
</option>

</select>

</div>


<!-- Duration -->

<div class="form-group">

<label>
    Duration
</label>

<input
    type="text"
    name="duration"
    placeholder="e.g. 6-8 Hours"
    required
>

</div>


<!-- Description -->

<div class="form-group full-width">

<label>
    Description
</label>

<textarea
    name="description"
    placeholder="Describe this adventure..."
    required
></textarea>

</div>


<!-- Image -->

<div class="form-group full-width">

<label>
    Adventure Image
</label>

<input
    type="file"
    name="image"
    id="image"
    accept=".jpg,.jpeg,.png,.webp"
    required
>

<img
    id="imagePreview"
    class="image-preview"
    alt="Image Preview"
>

</div>


</div>


<button
    type="submit"
    class="submit-button"
>
    Add Adventure →
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

        const file = this.files[0];

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