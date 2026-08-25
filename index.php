<?php

session_start();
require_once "config/db.php";

$adventures = [];
$selected_category = trim($_GET['category'] ?? '');

if ($selected_category !== '') {
    $stmt = $conn->prepare("\n        SELECT\n            a.adventure_id, a.adventure_name, a.description, a.price,\n            a.difficulty_level, a.duration, a.capacity,\n            c.category_name, l.location_name, l.district, ai.image_url\n        FROM adventures a\n        INNER JOIN categories c ON a.category_id = c.category_id\n        INNER JOIN locations l ON a.location_id = l.location_id\n        LEFT JOIN adventure_images ai\n            ON a.adventure_id = ai.adventure_id AND ai.is_main = TRUE\n        WHERE c.category_name = ?\n        ORDER BY a.created_at DESC\n    ");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("\n        SELECT\n            a.adventure_id, a.adventure_name, a.description, a.price,\n            a.difficulty_level, a.duration, a.capacity,\n            c.category_name, l.location_name, l.district, ai.image_url\n        FROM adventures a\n        INNER JOIN categories c ON a.category_id = c.category_id\n        INNER JOIN locations l ON a.location_id = l.location_id\n        LEFT JOIN adventure_images ai\n            ON a.adventure_id = ai.adventure_id AND ai.is_main = TRUE\n        ORDER BY a.created_at DESC\n    ");
}

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $adventures[] = $row;
    }
}

if (isset($stmt)) {
    $stmt->close();
}

$category_cards = [
    [
        'name' => 'Hiking',
        'icon' => '🥾',
        'image' => 'https://images.unsplash.com/photo-1551632811-561732d1e306?auto=format&fit=crop&w=900&q=85',
        'text' => 'Discover scenic trails, mountain views and unforgettable climbs.'
    ],
    [
        'name' => 'Camping',
        'icon' => '⛺',
        'image' => 'https://images.unsplash.com/photo-1478131143081-80f7f84ca84d?auto=format&fit=crop&w=900&q=85',
        'text' => 'Slow down, reconnect with nature and sleep beneath the stars.'
    ],
    [
        'name' => 'Water Sports',
        'icon' => '🌊',
        'image' => 'https://images.unsplash.com/photo-1530053969600-caed2596d242?auto=format&fit=crop&w=900&q=85',
        'text' => 'Feel the rush with rafting, rivers and exciting water adventures.'
    ],
    [
        'name' => 'Rock Climbing',
        'icon' => '🧗',
        'image' => 'https://images.unsplash.com/photo-1522163182402-834f871fd851?auto=format&fit=crop&w=900&q=85',
        'text' => 'Push your limits and take on new heights with confidence.'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ExploreX - Discover unforgettable adventure experiences across Sri Lanka.">
    <title>ExploreX | Discover Your Next Adventure</title>
    <link rel="stylesheet" href="assets/css/style.css?v=20260825">
</head>
<body>

<?php
$base_path = "";
require __DIR__ . "/includes/navbar.php";
?>

<!-- HERO -->
<section class="hero home-hero">
    <div class="hero-glow hero-glow-one"></div>
    <div class="hero-glow hero-glow-two"></div>
    <div class="hero-content">
        <p class="eyebrow">EXPLORE • EXPERIENCE • REMEMBER</p>
        <h1>DISCOVER YOUR <span>NEXT ADVENTURE.</span></h1>
        <p class="hero-description">
            Explore breathtaking destinations, thrilling activities and unforgettable experiences across Sri Lanka — all in one place.
        </p>
        <div class="hero-buttons">
            <a href="#adventures" class="primary-button">EXPLORE ADVENTURES →</a>
            <a href="#categories" class="secondary-button">BROWSE CATEGORIES</a>
        </div>
        <div class="hero-stats">
            <div><strong>01</strong><span>DISCOVER</span></div>
            <div><strong>02</strong><span>EXPERIENCE</span></div>
            <div><strong>03</strong><span>REMEMBER</span></div>
        </div>
    </div>

    <div class="hero-visual" aria-hidden="true">
        <div class="hero-orbit orbit-one"></div>
        <div class="hero-orbit orbit-two"></div>
        <div class="hero-image-card">
            <img src="https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1100&q=90" alt="Sri Lankan mountain landscape">
            <div class="hero-image-overlay"></div>
            <div class="hero-image-caption">
                <span>EXPLORE SRI LANKA</span>
                <strong>THE JOURNEY STARTS HERE.</strong>
            </div>
        </div>
        <div class="floating-badge badge-top">✦ WILD & FREE</div>
        <div class="floating-badge badge-bottom">📍 ISLAND ADVENTURES</div>
    </div>
</section>

<!-- CATEGORIES -->
<section class="categories" id="categories">
    <div class="section-heading centered-heading">
        <p class="eyebrow">FIND YOUR EXPERIENCE</p>
        <h2>EXPLORE BY CATEGORY</h2>
        <p>Choose a style of adventure and jump straight to experiences that match your mood.</p>
    </div>

    <div class="category-grid">
        <?php foreach ($category_cards as $category): ?>
            <a class="category-card glass-card" href="index.php?category=<?php echo urlencode($category['name']); ?>#adventures">
                <div class="category-image" style="background-image: linear-gradient(180deg, rgba(8,10,8,.05), rgba(8,10,8,.92)), url('<?php echo htmlspecialchars($category['image']); ?>');">
                    <span class="category-icon"><?php echo $category['icon']; ?></span>
                    <span class="category-arrow">↗</span>
                </div>
                <div class="category-content">
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['text']); ?></p>
                    <span class="category-link">VIEW ADVENTURES →</span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ADVENTURES -->
<section class="adventures" id="adventures">
    <div class="section-heading adventure-heading-row">
        <div>
            <p class="eyebrow">POPULAR DESTINATIONS</p>
            <h2><?php echo $selected_category ? htmlspecialchars(strtoupper($selected_category)) : 'FEATURED ADVENTURES'; ?></h2>
        </div>
        <?php if ($selected_category): ?>
            <a class="clear-filter" href="index.php#adventures">VIEW ALL →</a>
        <?php endif; ?>
    </div>

    <div class="adventure-grid">
        <?php if (count($adventures) > 0): ?>
            <?php foreach ($adventures as $adventure): ?>
                <?php $image = $adventure['image_url'] ?: 'kunckles.png'; ?>
                <a href="pages/adventure-details.php?id=<?php echo (int)$adventure['adventure_id']; ?>" class="adventure-card">
                    <div class="adventure-image" style="background-image: linear-gradient(to top, rgba(0,0,0,.84), transparent 70%), url('assets/images/<?php echo htmlspecialchars($image); ?>');">
                        <span><?php echo htmlspecialchars(strtoupper($adventure['category_name'])); ?></span>
                    </div>
                    <div class="adventure-content">
                        <p class="location">📍 <?php echo htmlspecialchars($adventure['location_name']); ?>, <?php echo htmlspecialchars($adventure['district']); ?></p>
                        <h3><?php echo htmlspecialchars($adventure['adventure_name']); ?></h3>
                        <p><?php echo htmlspecialchars($adventure['description']); ?></p>
                        <div class="adventure-footer">
                            <span><?php echo htmlspecialchars(strtoupper($adventure['difficulty_level'])); ?></span>
                            <span>RS. <?php echo number_format($adventure['price'], 2); ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-home-state glass-card">
                <div class="category-icon">🌿</div>
                <h3>NO ADVENTURES FOUND</h3>
                <p>There are no adventures in this category yet. Try another category.</p>
                <a href="index.php#adventures" class="primary-button">VIEW ALL ADVENTURES</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ABOUT -->
<section class="about about-section" id="about">
    <div class="about-image-wrap">
        <img src="https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1100&q=90" alt="Tropical Sri Lankan landscape">
        <div class="about-image-tag">MADE FOR ADVENTURE SEEKERS</div>
    </div>
    <div class="about-content">
        <p class="eyebrow">ABOUT EXPLOREX</p>
        <h2>YOUR JOURNEY <span>STARTS HERE.</span></h2>
        <p>
            ExploreX is an adventure discovery and booking platform created to make exploring Sri Lanka simple, exciting and accessible.
        </p>
        <p>
            From peaceful mountain trails and outdoor camping to river rafting and challenging climbs, ExploreX brings memorable experiences together so you can discover a place, choose an adventure and make your next trip count.
        </p>
        <div class="about-points">
            <div><strong>01</strong><span>DISCOVER LOCAL EXPERIENCES</span></div>
            <div><strong>02</strong><span>BOOK WITH CONFIDENCE</span></div>
            <div><strong>03</strong><span>CREATE UNFORGETTABLE MEMORIES</span></div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="site-footer">
    <div class="footer-main">
        <div class="footer-brand">
            <div class="logo">Explore<span>X</span></div>
            <p>Explore • Experience • Remember.</p>
            <p class="footer-small">Your gateway to unforgettable outdoor experiences across Sri Lanka.</p>
        </div>
        <div class="footer-column">
            <h4>EXPLORE</h4>
            <a href="index.php">HOME</a>
            <a href="#categories">CATEGORIES</a>
            <a href="#adventures">ADVENTURES</a>
            <a href="#about">ABOUT US</a>
        </div>
        <div class="footer-column">
            <h4>ACCOUNT</h4>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="pages/my-bookings.php">MY BOOKINGS</a>
                <a href="auth/logout.php">LOGOUT</a>
            <?php else: ?>
                <a href="auth/login.php">LOGIN</a>
                <a href="auth/register.php">CREATE ACCOUNT</a>
            <?php endif; ?>
        </div>
        <div class="footer-column">
            <h4>CONTACT</h4>
            <a href="mailto:hello@explorex.lk">hello@explorex.lk</a>
            <span>SRI LANKA</span>
            <span>MON — SUN • 09:00 — 18:00</span>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© 2026 EXPLOREX. ALL RIGHTS RESERVED.</span>
        <span>EXPLORE • EXPERIENCE • REMEMBER</span>
    </div>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
