<?php
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

<div class="admin-back-wrap"><a class="admin-back-link" href="dashboard.php">← BACK TO DASHBOARD</a></div>
<main class="manage-users-page"><p class="eyebrow">EXPLOREX ADMIN</p><h1>MANAGE USERS</h1><p>View registered ExploreX accounts and their roles.</p>
<div class="users-wrap"><table><thead><tr><th>ID</th><th>NAME</th><th>EMAIL</th><th>PHONE</th><th>ROLE</th><th>JOINED</th></tr></thead><tbody>
<?php if($result && $result->num_rows): while($user=$result->fetch_assoc()): ?><tr><td>#<?php echo (int)$user['user_id']; ?></td><td><?php echo htmlspecialchars($user['name']); ?></td><td><?php echo htmlspecialchars($user['email']); ?></td><td><?php echo htmlspecialchars($user['phone'] ?: '—'); ?></td><td><span class="role"><?php echo htmlspecialchars($user['role']); ?></span></td><td><?php echo date('d M Y',strtotime($user['created_at'])); ?></td></tr><?php endwhile; else: ?><tr><td colspan="6" style="text-align:center;padding:35px;color:#9da49a">No users found.</td></tr><?php endif; ?>
</tbody></table></div></main></body></html>