from pathlib import Path
root=Path('/mnt/data/explorex_edit')
css=root/'assets/css/style.css'
s=css.read_text()
s += r'''

/* =========================================================
   EXPLOREX VISUAL UPDATE 2026-08-25
========================================================= */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap');

:root {
    --heading-font: 'Space Grotesk', Arial, sans-serif;
    --body-font: 'DM Sans', Arial, sans-serif;
}

body { font-family: var(--body-font); }
h1,h2,h3,h4,h5,h6,.logo,.nav-links,.eyebrow { font-family: var(--heading-font); }
h1,h2,h3,h4,h5,h6 { text-transform: uppercase; }

.hero h1 {
    font-family: var(--heading-font);
    font-size: clamp(46px, 6.2vw, 78px);
    letter-spacing: -3px;
    text-transform: uppercase;
    text-wrap: balance;
    animation: heroTitleFloat 5s ease-in-out infinite;
}
.hero h1 span {
    display: inline-block;
    background: linear-gradient(100deg, var(--olive-light), #e8efcf, var(--olive));
    background-size: 220% auto;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: titleShimmer 4s linear infinite;
}
.hero-content .eyebrow { animation: softPulse 3s ease-in-out infinite; }

@keyframes heroTitleFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
@keyframes titleShimmer { to { background-position: 220% center; } }
@keyframes softPulse { 0%,100% { opacity:.72; } 50% { opacity:1; } }
@keyframes floatCard { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
@keyframes iconPop { 0%,100% { transform: scale(1) rotate(0); } 50% { transform: scale(1.08) rotate(-3deg); } }

.section-heading h2, .about h2 { font-size: clamp(30px, 4vw, 48px); letter-spacing: -1.5px; }
.section-heading { margin-bottom: 32px; }

.adventure-grid { gap: 18px; }
.adventure-card { border-radius: 20px; }
.adventure-image { height: 220px; }
.adventure-content { padding: 19px; }
.adventure-content h3 { font-size: 19px; }

/* Admin dashboard cards */
.admin-page .admin-header h1 { font-size: clamp(34px, 4vw, 52px); }
.admin-page .section-heading h2 { font-size: clamp(28px, 3.5vw, 42px); }
.stats-grid { gap: 14px !important; margin-bottom: 42px !important; }
.stat-card {
    display: block;
    position: relative;
    min-height: 128px;
    padding: 20px !important;
    border-radius: 18px !important;
    overflow: hidden;
    transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease;
}
.stat-card::after {
    content: '';
    position: absolute;
    width: 90px; height: 90px;
    right: -30px; bottom: -35px;
    border-radius: 50%;
    background: rgba(181,200,137,.10);
    filter: blur(4px);
}
.stat-card:hover { transform: translateY(-5px); border-color: rgba(181,200,137,.4); box-shadow: 0 14px 35px rgba(0,0,0,.2); }
.stat-card h2 { font-size: 30px !important; }
.stat-card .stat-icon { font-size: 22px; margin-bottom: 7px; animation: iconPop 3s ease-in-out infinite; }

.actions-grid { gap: 14px !important; margin-bottom: 45px !important; }
.action-card {
    min-height: 130px;
    padding: 19px !important;
    border-radius: 18px !important;
    position: relative;
    overflow: hidden;
}
.action-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(181,200,137,.08), transparent 55%);
    pointer-events: none;
}
.action-card:hover { transform: translateY(-6px) scale(1.01); box-shadow: 0 15px 35px rgba(0,0,0,.2); }
.action-icon {
    width: 42px; height: 42px;
    display: grid; place-items: center;
    margin-bottom: 11px !important;
    border: 1px solid rgba(181,200,137,.2);
    border-radius: 13px;
    background: rgba(181,200,137,.08);
    font-size: 21px !important;
    animation: iconPop 3.5s ease-in-out infinite;
}
.action-card h3 { font-size: 16px; }
.action-card p { font-size: 12px !important; }

/* Adventure detail gallery */
.details-page { padding: 115px 7% 65px !important; }
.details-container { grid-template-columns: minmax(0,1.02fr) minmax(360px,.98fr) !important; gap: 34px !important; align-items: start !important; }
.details-content h1 { font-size: clamp(34px, 4.2vw, 54px) !important; letter-spacing: -2px; }
.details-image-gallery { display: grid; grid-template-columns: minmax(0,1fr); gap: 10px; }
.details-main-image-wrap { height: 420px; overflow: hidden; border-radius: 22px; border: 1px solid var(--border); background: #101410; }
.details-main-image { width:100%; height:100%; object-fit:cover; display:block; transition: opacity .25s ease, transform .4s ease; }
.details-main-image:hover { transform: scale(1.02); }
.details-thumbs { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
.details-thumb { height:76px; padding:0; border:1px solid rgba(255,255,255,.1); border-radius:12px; overflow:hidden; background:transparent; cursor:pointer; opacity:.68; transition:.25s; }
.details-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.details-thumb:hover,.details-thumb.active { opacity:1; border-color:var(--olive-light); transform:translateY(-2px); }
.info-box { padding: 13px !important; }
.info-box strong { font-size: 13px !important; }
.price { font-size: 26px !important; }
.book-button { width:auto !important; min-width:210px; padding:13px 22px !important; font-size:14px !important; margin-left:auto; }

@media (max-width: 850px) {
    .details-container { grid-template-columns:1fr !important; }
    .details-main-image-wrap { height:340px; }
}
@media (max-width: 600px) {
    .hero h1 { font-size: 44px; letter-spacing:-2px; }
    .details-main-image-wrap { height:280px; }
    .details-thumbs { grid-template-columns:repeat(4,1fr); }
    .details-thumb { height:60px; }
}
'''
css.write_text(s)

# Replace dashboard stat blocks with links + icons
p=root/'admin/dashboard.php'; s=p.read_text()
old='''        <div class="stat-card">\n\n            <p>\n                TOTAL USERS\n            </p>\n\n            <h2>\n                <?php echo $total_users; ?>\n            </h2>\n\n        </div>\n\n\n        <div class="stat-card">\n\n            <p>\n                ADVENTURES\n            </p>\n\n            <h2>\n                <?php echo $total_adventures; ?>\n            </h2>\n\n        </div>\n\n\n        <div class="stat-card">\n\n            <p>\n                TOTAL BOOKINGS\n            </p>\n\n            <h2>\n                <?php echo $total_bookings; ?>\n            </h2>\n\n        </div>\n\n\n        <div class="stat-card">\n\n            <p>\n                PENDING BOOKINGS\n            </p>\n\n            <h2>\n                <?php echo $pending_bookings; ?>\n            </h2>\n\n        </div>'''
new='''        <a href="manage-users.php" class="stat-card">\n            <div class="stat-icon">👥</div>\n            <p>TOTAL USERS</p>\n            <h2><?php echo $total_users; ?></h2>\n        </a>\n\n        <a href="manage-adventures.php" class="stat-card">\n            <div class="stat-icon">🏔️</div>\n            <p>ADVENTURES</p>\n            <h2><?php echo $total_adventures; ?></h2>\n        </a>\n\n        <a href="manage-bookings.php" class="stat-card">\n            <div class="stat-icon">📋</div>\n            <p>TOTAL BOOKINGS</p>\n            <h2><?php echo $total_bookings; ?></h2>\n        </a>\n\n        <a href="manage-bookings.php?status=PENDING" class="stat-card">\n            <div class="stat-icon">⏳</div>\n            <p>PENDING BOOKINGS</p>\n            <h2><?php echo $pending_bookings; ?></h2>\n        </a>'''
if old not in s: raise SystemExit('dashboard stat block not found')
s=s.replace(old,new)
# action icon labels more polished
s=s.replace('<div class="action-icon">\n                ➕\n            </div>','<div class="action-icon">＋</div>')
s=s.replace('<div class="action-icon">\n                🏕️\n            </div>','<div class="action-icon">⌂</div>')
s=s.replace('<div class="action-icon">\n                📋\n            </div>','<div class="action-icon">☷</div>')
p.write_text(s)

# Manage bookings: optional status filter
p=root/'admin/manage-bookings.php'; s=p.read_text()
old='''// Get bookings\n\n$stmt = $conn->prepare("\n    SELECT'''
new='''// Get bookings\n$filter_status = isset($_GET["status"]) ? strtoupper(trim($_GET["status"])) : "";\n$allowed_filters = ["PENDING", "CONFIRMED", "CANCELLED"];\n\nif (in_array($filter_status, $allowed_filters, true)) {\n    $stmt = $conn->prepare("\n        SELECT\n\n            b.booking_id,\n            b.booking_date,\n            b.participants,\n            b.total_amount,\n            b.status,\n\n            u.user_id,\n            u.name AS user_name,\n            u.email AS user_email,\n\n            a.adventure_id,\n            a.adventure_name,\n\n            l.location_name,\n            l.district\n\n        FROM bookings b\n\n        INNER JOIN users u ON b.user_id = u.user_id\n        INNER JOIN adventures a ON b.adventure_id = a.adventure_id\n        INNER JOIN locations l ON a.location_id = l.location_id\n\n        WHERE b.status = ?\n        ORDER BY b.booking_date DESC\n    ");\n    $stmt->bind_param("s", $filter_status);\n} else {\n    $stmt = $conn->prepare("\n        SELECT'''
if old not in s: raise SystemExit('manage booking marker not found')
s=s.replace(old,new,1)
# Need close else query's original ending; current original ends LIMIT? let's inspect exact and patch after ORDER
# original block has ORDER BY then closing ");. We inserted else but need its close brace after prepare.
needle='''    ORDER BY\n        b.booking_date DESC\n");\n\n$stmt->execute();'''
replacement='''    ORDER BY\n        b.booking_date DESC\n    ");\n}\n\n$stmt->execute();'''
if needle not in s: raise SystemExit('manage booking end marker not found')
s=s.replace(needle,replacement,1)
# header title dynamic
s=s.replace('<h1>\n\n    Manage Bookings\n\n</h1>','<h1><?php echo $filter_status ? htmlspecialchars($filter_status) . " BOOKINGS" : "MANAGE BOOKINGS"; ?></h1>')
p.write_text(s)

# Create manage users page
(root/'admin/manage-users.php').write_text(r'''<?php
session_start();
require_once "../config/db.php";
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "ADMIN") {
    header("Location: ../index.php"); exit();
}
$result = $conn->query("SELECT user_id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users | ExploreX</title><link rel="stylesheet" href="../assets/css/style.css">
<style>
.manage-users-page{padding:115px 7% 70px}.manage-users-page h1{font-size:clamp(34px,4vw,50px);letter-spacing:-1.5px;margin-bottom:8px}.manage-users-page>p{color:#9da49a;margin-bottom:28px}.users-wrap{overflow:auto;border:1px solid rgba(255,255,255,.1);border-radius:20px;background:rgba(255,255,255,.04);backdrop-filter:blur(20px)}table{width:100%;min-width:760px;border-collapse:collapse}th,td{text-align:left;padding:15px 17px}th{font-size:11px;letter-spacing:1px;color:#b5c889;background:rgba(255,255,255,.04)}td{font-size:13px;color:#d8ddd3;border-top:1px solid rgba(255,255,255,.07)}.role{display:inline-block;padding:5px 9px;border-radius:12px;background:rgba(181,200,137,.1);color:#b5c889;font-size:10px;font-weight:700}
</style></head><body>
<?php $base_path="../"; require __DIR__ . "/../includes/navbar.php"; ?>
<main class="manage-users-page"><p class="eyebrow">EXPLOREX ADMIN</p><h1>MANAGE USERS</h1><p>View registered ExploreX accounts and their roles.</p>
<div class="users-wrap"><table><thead><tr><th>ID</th><th>NAME</th><th>EMAIL</th><th>PHONE</th><th>ROLE</th><th>JOINED</th></tr></thead><tbody>
<?php if($result && $result->num_rows): while($user=$result->fetch_assoc()): ?><tr><td>#<?php echo (int)$user['user_id']; ?></td><td><?php echo htmlspecialchars($user['name']); ?></td><td><?php echo htmlspecialchars($user['email']); ?></td><td><?php echo htmlspecialchars($user['phone'] ?: '—'); ?></td><td><span class="role"><?php echo htmlspecialchars($user['role']); ?></span></td><td><?php echo date('d M Y',strtotime($user['created_at'])); ?></td></tr><?php endwhile; else: ?><tr><td colspan="6" style="text-align:center;padding:35px;color:#9da49a">No users found.</td></tr><?php endif; ?>
</tbody></table></div></main></body></html>''')

# Rewrite details file via targeted replacements
p=root/'pages/adventure-details.php'; s=p.read_text()
# Add all images query after main stmt close
needle='''$stmt->close();\n\n?>'''
insert='''$stmt->close();\n\n// Load every image for the adventure so the details page has a gallery.\n$image_stmt = $conn->prepare("\n    SELECT image_url, is_main\n    FROM adventure_images\n    WHERE adventure_id = ?\n    ORDER BY is_main DESC, image_id ASC\n");\n$image_stmt->bind_param("i", $adventure_id);\n$image_stmt->execute();\n$image_result = $image_stmt->get_result();\n$gallery_images = [];\nwhile ($image_row = $image_result->fetch_assoc()) {\n    if (!empty($image_row["image_url"])) {\n        $gallery_images[] = $image_row["image_url"];\n    }\n}\n$image_stmt->close();\n\nif (empty($gallery_images) && !empty($adventure["image_url"])) {\n    $gallery_images[] = $adventure["image_url"];\n}\n\n?>'''
if needle not in s: raise SystemExit('details close marker not found')
s=s.replace(needle,insert,1)
# replace image block
start=s.index('<!-- IMAGE -->')
end=s.index('<!-- CONTENT -->')
newblock='''<!-- IMAGE GALLERY -->\n<div class="details-image-gallery">\n    <?php if (!empty($gallery_images)): ?>\n        <div class="details-main-image-wrap">\n            <img\n                id="detailsMainImage"\n                class="details-main-image"\n                src="../assets/images/<?php echo htmlspecialchars($gallery_images[0]); ?>"\n                alt="<?php echo htmlspecialchars($adventure["adventure_name"]); ?>"\n            >\n        </div>\n        <?php if (count($gallery_images) > 1): ?>\n            <div class="details-thumbs">\n                <?php foreach ($gallery_images as $index => $gallery_image): ?>\n                    <button type="button" class="details-thumb <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showAdventureImage(this, '<?php echo htmlspecialchars('../assets/images/' . $gallery_image, ENT_QUOTES); ?>')">\n                        <img src="../assets/images/<?php echo htmlspecialchars($gallery_image); ?>" alt="Adventure image <?php echo $index + 1; ?>">\n                    </button>\n                <?php endforeach; ?>\n            </div>\n        <?php endif; ?>\n    <?php else: ?>\n        <div class="details-main-image-wrap" style="display:grid;place-items:center;color:#777">NO IMAGE</div>\n    <?php endif; ?>\n</div>\n\n\n'''
s=s[:start]+newblock+s[end:]
# add script before </body>
s=s.replace('</body>','''<script>\nfunction showAdventureImage(button, src) {\n    const main = document.getElementById('detailsMainImage');\n    if (!main) return;\n    main.style.opacity = '0.35';\n    setTimeout(() => { main.src = src; main.style.opacity = '1'; }, 120);\n    document.querySelectorAll('.details-thumb').forEach(el => el.classList.remove('active'));\n    button.classList.add('active');\n}\n</script>\n\n</body>''')
p.write_text(s)
