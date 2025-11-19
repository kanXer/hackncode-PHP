<?php
session_start();
include "db.php";

$error = "";

// Normal Admin Login
if (isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = $_POST['password'];

    $q = $conn->prepare("SELECT id, password FROM admin_users WHERE username=? LIMIT 1");
    $q->bind_param("s", $u);
    $q->execute();
    $q->store_result();
    $q->bind_result($id, $hash);

    if ($q->num_rows > 0) {
        $q->fetch();
        if (password_verify($p, $hash)) {
            $_SESSION['admin'] = $id;
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "❌ Wrong password!";
        }
    } else {
        $error = "❌ Username not found!";
    }
}

// Secret Access for Creating Admin
$MASTER_KEY = "kanxer@master";  // Change this to your secret phrase

if (isset($_POST['add_admin'])) {
    $key = $_POST['master_key'];
    if ($key === $MASTER_KEY) {
        header("Location: add_admin.php");
        exit;
    } else {
        $error = "❌ Incorrect master key!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Login</title>
<link rel="icon" type="image/svg+xml"
href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">


<link rel="stylesheet" href="/assets/admin.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="login-box">
    <h2>Admin Login</h2>

    <?php if($error): ?>
        <p><?=$error?></p>
    <?php endif; ?>

    <!-- Normal Login Form -->
    <form method="post">
        <input type="text" name="username" placeholder="Enter Username" required>
        <input type="password" name="password" placeholder="Enter Password" required>
        <button name="login">Login</button>
    </form>

    <hr style="margin:20px 0; border-color:#333;">

    <!-- Add Admin Button (Protected with Master Key) -->
    <form method="post">
        <input type="password" name="master_key" placeholder="Master Key (For Creating Admin)" required>
        <button name="add_admin" style="background:#294eff;">+ Add New Admin User</button>
    </form>

</div>

</body>
</html>
