<?php
session_start();
include "db.php";

// ✔ If you want only logged-in admin to add new admin, keep this


$error = "";
$success = "";

// When form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === "" || $password === "") {
        $error = "⚠ All fields are required!";
    } else {

        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "⚠ Username already exists!";
        } else {

            // Create password hash (bcrypt)
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert into DB
            $add = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
            $add->bind_param("ss", $username, $hash);

            if ($add->execute()) {
                $success = "✅ New admin user created!";
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/svg+xml"
href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' ry='20' fill='%23050705'/%3E%3Ctext x='50' y='63' font-size='42' font-family='Inter' text-anchor='middle' fill='%2314b866' font-weight='700'%3ESKS%3C/text%3E%3C/svg%3E">

    <title>Add Admin User</title>
    <link rel="stylesheet" href="/assets/admin.css">
    <style>
        .back-btn { 
            text-decoration:none; 
            color:#fff; 
            display:inline-block; 
            margin-bottom:10px; 
            background:#444; 
            padding:7px 14px; 
            border-radius:6px;
        }
    </style>
</head>
<body>

<div class="dash-box">

    <h2>Add New Admin User</h2>
    <a class="back-btn" href="admin_login.php">← Back to Login</a>

    <?php if ($error): ?>
        <p class="err"><?= $error ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <form method="POST" class="news-form">
        <input type="text" name="username" placeholder="New Admin Username" required>
        <input type="password" name="password" placeholder="Admin Password" required>
        <button type="submit">Create Admin</button>
    </form>

</div>

</body>
</html>
