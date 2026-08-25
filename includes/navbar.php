<?php
// Shared ExploreX navigation bar.
// Set $base_path before including this file:
// '' for index.php, '../' for pages/ and admin/ pages.
$base_path = $base_path ?? '';
?>

<nav class="navbar">

    <a href="<?php echo $base_path; ?>index.php" class="logo">
        Explore<span>X</span>
    </a>

    <button class="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="nav-links">

        <a href="<?php echo $base_path; ?>index.php">
            Home
        </a>

        <a href="<?php echo $base_path; ?>index.php#adventures">
            Adventures
        </a>

        <a href="<?php echo $base_path; ?>index.php#about">
            About
        </a>

        <?php if (isset($_SESSION["user_id"])): ?>

            <?php if (isset($_SESSION["role"]) && $_SESSION["role"] === "ADMIN"): ?>

                <a href="<?php echo $base_path; ?>admin/dashboard.php">
                    Dashboard
                </a>

            <?php else: ?>

                <a href="<?php echo $base_path; ?>pages/my-bookings.php">
                    My Booking
                </a>

            <?php endif; ?>

            <a href="<?php echo $base_path; ?>auth/logout.php">
                Logout
            </a>

        <?php else: ?>

            <a href="<?php echo $base_path; ?>auth/login.php">
                Login
            </a>

            <a href="<?php echo $base_path; ?>auth/register.php" class="nav-button">
                Get Started
            </a>

        <?php endif; ?>

    </div>

</nav>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const navbar = document.querySelector(".navbar");
    if (!navbar) return;
    const toggle = navbar.querySelector(".menu-toggle");
    const links = navbar.querySelector(".nav-links");
    if (!toggle || !links) return;

    toggle.addEventListener("click", function () {
        const open = navbar.classList.toggle("menu-open");
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        toggle.setAttribute("aria-label", open ? "Close navigation" : "Open navigation");
    });

    links.querySelectorAll("a").forEach(function (link) {
        link.addEventListener("click", function () {
            navbar.classList.remove("menu-open");
            toggle.setAttribute("aria-expanded", "false");
            toggle.setAttribute("aria-label", "Open navigation");
        });
    });
});
</script>
