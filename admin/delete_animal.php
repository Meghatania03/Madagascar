<?php
session_start();
$conn = mysqli_connect("localhost:3307","root","","Madagascar_db");
if (!$conn) die("Connection failed");

if(!isset($_SESSION['user_type']) || $_SESSION['user_type']!=='admin'){
    header("Location: login.php"); exit;
}

$id = $_GET['id'];
mysqli_query($conn,"DELETE FROM Animal WHERE animal_id=$id");
header("Location: admin_dashboard.php");
exit;
?>
