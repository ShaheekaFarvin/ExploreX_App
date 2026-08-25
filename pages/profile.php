<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SESSION["role"] !== "USER") {
    header("Location: ../admin/dashboard.php");
    exit();
}

$user_id=(int)$_SESSION["user_id"];
$message=""; $message_type="";

$stmt=$conn->prepare("SELECT name,email,phone FROM users WHERE user_id=? LIMIT 1");
$stmt->bind_param("i",$user_id); $stmt->execute();
$user=$stmt->get_result()->fetch_assoc(); $stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name=trim($_POST["name"] ?? "");
    $phone=trim($_POST["phone"] ?? "");
    $password=$_POST["password"] ?? "";
    if ($name === "" || strlen($name)<2 || strlen($name)>100) {
        $message="Please enter a valid name."; $message_type="error";
    } elseif ($phone !== "" && !preg_match('/^[0-9+()\-\s]{7,20}$/',$phone)) {
        $message="Please enter a valid phone number."; $message_type="error";
    } elseif ($password !== "" && strlen($password)<6) {
        $message="New password must contain at least 6 characters."; $message_type="error";
    } else {
        if ($password !== "") {
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $up=$conn->prepare("UPDATE users SET name=?, phone=?, password=? WHERE user_id=?");
            $up->bind_param("sssi",$name,$phone,$hash,$user_id);
        } else {
            $up=$conn->prepare("UPDATE users SET name=?, phone=? WHERE user_id=?");
            $up->bind_param("ssi",$name,$phone,$user_id);
        }
        if ($up->execute()) {
            $_SESSION["name"]=$name;
            $message="Profile updated successfully."; $message_type="success";
            $user["name"]=$name; $user["phone"]=$phone;
        } else { $message="Unable to update profile. Please try again."; $message_type="error"; }
        $up->close();
    }
}

$base_path="../";
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile | ExploreX</title>
<link rel="stylesheet" href="../assets/css/style.css?v=profile3">
<style>
.profile-page{min-height:100vh;padding:125px 20px 70px;display:grid;place-items:start center}.profile-card{width:min(100%,650px);padding:30px;border:1px solid rgba(255,255,255,.1);border-radius:24px;background:rgba(255,255,255,.05);backdrop-filter:blur(22px);box-shadow:0 25px 70px rgba(0,0,0,.22)}.profile-top{display:flex;align-items:center;gap:18px;margin-bottom:25px}.profile-avatar{width:68px;height:68px;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#8b9b62,#26301d);font-size:27px;font-weight:800;color:#10120d;box-shadow:0 0 35px rgba(181,200,137,.18)}.profile-top h1{font-size:34px;line-height:1;margin:5px 0}.profile-top p{color:#9da49a;font-size:12px}.profile-form{display:grid;gap:16px}.profile-form label{display:block;color:#dfe4da;font-size:12px;font-weight:700;margin-bottom:7px}.profile-form input{width:100%;box-sizing:border-box;padding:12px 13px;border:1px solid rgba(255,255,255,.1);border-radius:12px;background:rgba(0,0,0,.22);color:#fff;outline:none}.profile-form input:focus{border-color:#b5c889;box-shadow:0 0 0 3px rgba(181,200,137,.08)}.profile-email{opacity:.55}.profile-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:5px}.profile-save,.profile-back{padding:10px 16px;border-radius:20px;font-size:12px;text-decoration:none}.profile-save{border:0;background:#8b9b62;color:#10120d;font-weight:800}.profile-back{border:1px solid rgba(255,255,255,.12);color:#ddd}.profile-msg{padding:11px 13px;border-radius:12px;font-size:12px;margin-bottom:15px}.profile-msg.success{color:#c7dda3;background:rgba(139,155,98,.12)}.profile-msg.error{color:#f0b0b0;background:rgba(180,70,70,.1)}@media(max-width:600px){.profile-card{padding:22px}.profile-top h1{font-size:28px}.profile-actions{flex-direction:column}.profile-save,.profile-back{text-align:center}}
</style></head><body>
<?php require __DIR__ . "/../includes/navbar.php"; ?>
<main class="profile-page"><section class="profile-card">
<div class="profile-top"><div class="profile-avatar"><?php echo strtoupper(substr($user["name"] ?? "U",0,1)); ?></div><div><p class="eyebrow">MY EXPLOREX</p><h1>MY PROFILE</h1><p>Update your account details securely.</p></div></div>
<?php if($message): ?><div class="profile-msg <?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
<form method="POST" class="profile-form">
<div><label>FULL NAME</label><input type="text" name="name" value="<?php echo htmlspecialchars($user["name"] ?? ""); ?>" maxlength="100" required></div>
<div><label>EMAIL</label><input class="profile-email" type="email" value="<?php echo htmlspecialchars($user["email"] ?? ""); ?>" readonly></div>
<div><label>PHONE</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($user["phone"] ?? ""); ?>" maxlength="20" placeholder="07X XXX XXXX"></div>
<div><label>NEW PASSWORD <span style="color:#777;font-weight:400">(OPTIONAL)</span></label><input type="password" name="password" minlength="6" placeholder="Leave blank to keep current password"></div>
<div class="profile-actions"><a class="profile-back" href="my-bookings.php">← MY BOOKINGS</a><button class="profile-save" type="submit">SAVE CHANGES</button></div>
</form></section></main></body></html>