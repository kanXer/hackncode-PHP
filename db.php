<?php
$host = "sql212.infinityfree.com";
$user = "if0_40164900";
$pass = "itsLala210600";
$dbname = "if0_40164900_epiz_123456_kanxer";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("DB Connection Failed: " . $conn->connect_error);
}
?>
