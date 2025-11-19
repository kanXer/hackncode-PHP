<?php
session_start();
if (!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit; }
include "db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: admin_dashboard.php"); exit; }

// fetch images to delete files
$stmt = $conn->prepare("SELECT images FROM news WHERE id = ? LIMIT 1");
$stmt->bind_param("i",$id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if ($row) {
    if (!empty($row['images'])) {
        $imgs = json_decode($row['images'], true);
        if (is_array($imgs)) {
            foreach ($imgs as $f) {
                $path = __DIR__.'/uploads/'.$f;
                if (file_exists($path)) @unlink($path);
            }
        }
    }
}

// delete db record
$d = $conn->prepare("DELETE FROM news WHERE id = ? LIMIT 1");
$d->bind_param("i",$id);
$d->execute();

header("Location: admin_dashboard.php");
exit;
