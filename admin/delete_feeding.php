<?php
session_start();
$conn = mysqli_connect("localhost:3307","root","","Madagascar_db");
if (!$conn) die("Connection failed");

if(!isset($_SESSION['user_type']) || $_SESSION['user_type']!=='admin'){
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM Feeding_Schedule WHERE schedule_id=$id");
echo "<script>alert('Feeding schedule deleted successfully!');window.location='admin_dashboard.php';</script>";
?>
