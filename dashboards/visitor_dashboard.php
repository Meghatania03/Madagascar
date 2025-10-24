<?php
session_start();
$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'visitor') {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['username'];
$visitor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT visitor_id FROM Visitor WHERE email='$email'"));
$visitor_id = $visitor['visitor_id'];

$tickets = mysqli_query($conn, "SELECT * FROM Ticket WHERE visitor_id=$visitor_id");
$animals = mysqli_query($conn, "SELECT name, species, health_status FROM Animal");
?>

<!DOCTYPE html>
<html>
<head>
<title>Visitor Dashboard - Madagascar Zoo</title>
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
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
table, th, td {
    border: 1px solid #ccc;
}
th, td {
    padding: 10px;
    text-align: center;
}
th {
    background: #218c74;
    color: white;
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
<h1>🦜 Visitor Dashboard</h1>
<form method="POST" action="logout.php"><button class="logout">Logout</button></form>

<h2>Your Tickets</h2>
<!-- Book New Ticket Button -->
<form action="book_ticket.php" method="get" style="margin-bottom: 10px;">
    <button type="submit" style="
        background: #2d98da;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
    ">Book New Ticket</button>
</form>

<table>
<tr><th>Ticket ID</th><th>Price</th><th>Issue Date</th></tr>
<?php while($t = mysqli_fetch_assoc($tickets)) echo "<tr><td>{$t['ticket_id']}</td><td>{$t['price']}</td><td>{$t['issue_date']}</td></tr>"; ?>
</table>


<h2>Animals You Can See</h2>
<table>
<tr><th>Name</th><th>Species</th><th>Health Status</th></tr>
<?php while($a = mysqli_fetch_assoc($animals)) echo "<tr><td>{$a['name']}</td><td>{$a['species']}</td><td>{$a['health_status']}</td></tr>"; ?>
</table>

</body>
</html>
