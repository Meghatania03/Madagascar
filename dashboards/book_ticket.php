<?php
session_start();
$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Ensure visitor is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'visitor') {
    header("Location: ../auth/login.php");
    exit;
}

$email = $_SESSION['username'];
$visitor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT visitor_id FROM Visitor WHERE email='$email'"));
$visitor_id = $visitor['visitor_id'];

// Fixed price per ticket
$price_per_ticket = 50;

// Handle form submission
if (isset($_POST['book_ticket'])) {
    $quantity = (int)$_POST['quantity'];
    $visit_date = $_POST['visit_date'];

    for ($i = 0; $i < $quantity; $i++) {
        mysqli_query($conn, "INSERT INTO Ticket(visitor_id, price, issue_date, visit_date) 
                             VALUES($visitor_id, $price_per_ticket, NOW(), '$visit_date')");
    }

    echo "<script>alert('Ticket(s) booked successfully!');window.location='visitor_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Book New Ticket - Madagascar Zoo</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #d8e0c0ff, #9a9c9aff);
    margin: 0;
    padding: 20px;
}
h1 {
    text-align: center;
    color: #2d3436;
}
form {
    max-width: 400px;
    margin: 50px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.2);
}
input, select, button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
}
button {
    background: #2d98da;
    color: white;
    border: none;
    cursor: pointer;
}
button:hover {
    background: #227093;
}
.logout {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    float: right;
}
</style>
</head>
<body>

<h1>🦜 Book New Ticket</h1>
<form method="POST">
    <label for="quantity">Number of Tickets:</label>
    <input type="number" name="quantity" id="quantity" min="1" value="1" required>

    <label for="visit_date">Visit Date:</label>
    <input type="date" name="visit_date" id="visit_date" required>

    <label>Price per Ticket:</label>
    <input type="text" value="$<?= $price_per_ticket ?>" readonly>

    <button type="submit" name="book_ticket">Book Ticket</button>
</form>

<form method="POST" action="../auth/logout.php"><button class="logout">Logout</button></form>




</body>
</html>
