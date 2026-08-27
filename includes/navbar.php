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

            <a href="<?php echo $base_path; ?>auth/logout.php" class="nav-button nav-logout-button">
                Logout
            </a>

            <?php if (($_SESSION["role"] ?? "") !== "ADMIN"): ?>
                <a href="<?php echo $base_path; ?>pages/profile.php" class="nav-avatar-link" title="My Profile">
                    <span class="nav-avatar-circle">
                        <?php if (!empty($_SESSION["profile_photo"])): ?>
                            <img src="<?php echo $base_path . htmlspecialchars($_SESSION["profile_photo"]); ?>" alt="My profile" class="nav-avatar-img">
                        <?php else: ?>
                            <span class="nav-avatar-fallback"><?php echo strtoupper(substr($_SESSION["name"] ?? "U", 0, 1)); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-avatar-label">My Profile</span>
                </a>
            <?php endif; ?>

        <?php else: ?>

            <a href="<?php echo $base_path; ?>auth/login.php" class="nav-button nav-login-button">
                Login
            </a>

            <a href="<?php echo $base_path; ?>auth/register.php" class="nav-button">
                Get Started
            </a>

        <?php endif; ?>

    </div>

</nav>

<!-- Global foreground particles. Hidden on the home hero because the hero has its own particle field. -->
<div class="site-particle-field" aria-hidden="true">
    <span></span><span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span><span></span>
    <span></span><span></span><span></span><span></span><span></span><span></span>
</div>

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
