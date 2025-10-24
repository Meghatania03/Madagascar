<?php
session_start();
$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

// Restrict access to staff only
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: login.php");
    exit;
}

// Get logged-in staff ID
$email = $_SESSION['username'];
$staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT staff_id, name FROM Staff WHERE email='$email'"));
$staff_id = $staff['staff_id'];
$staff_name = $staff['name'];

// 1️⃣ Animals they are caretakers for
$animals = mysqli_query($conn, "
    SELECT a.animal_id, a.name, a.species, a.age, a.health_status, e.name AS enclosure_name
    FROM Animal a
    JOIN Enclosure e ON a.enclosure_id = e.enclosure_id
    WHERE a.caretaker_id = $staff_id
");

// 2️⃣ Feeding tasks assigned to them (feed_by)
$feedings = mysqli_query($conn, "
    SELECT fs.schedule_id, a.name AS animal_name, fs.food_name, fs.feeding_time, fs.quantity
    FROM Feeding_Schedule fs
    JOIN Animal a ON fs.animal_id = a.animal_id
    WHERE fs.feed_by = $staff_id
    ORDER BY fs.feeding_time
");

// 3️⃣ Medical records where they are vet
$medical = mysqli_query($conn, "
    SELECT mr.record_id, a.name AS animal_name, mr.diagnosis, mr.treatment, mr.checkup_date
    FROM Medical_Record mr
    JOIN Animal a ON mr.animal_id = a.animal_id
    WHERE mr.vet_id = $staff_id
    ORDER BY mr.checkup_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Staff Dashboard - Madagascar Zoo</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #a8e6cf, #dcedc1);
    margin: 0;
    padding: 20px;
}
h1, h2 {
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
    background: #27ae60;
    color: white;
}
.logout {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    float: right;
    cursor: pointer;
}
.logout:hover { background:#c0392b; }
.container { max-width: 1200px; margin: auto; }
</style>
</head>
<body>
<div class="container">
<h1>🐘 Staff Dashboard - <?= htmlspecialchars($staff_name) ?></h1>
<form method="POST" action="logout.php"><button class="logout">Logout</button></form>

<h2>Your Assigned Animals</h2>
<?php if(mysqli_num_rows($animals) > 0): ?>
<table>
<tr><th>Name</th><th>Species</th><th>Age</th><th>Health Status</th><th>Enclosure</th></tr>
<?php while($a = mysqli_fetch_assoc($animals)): ?>
<tr>
<td><?= htmlspecialchars($a['name']) ?></td>
<td><?= htmlspecialchars($a['species']) ?></td>
<td><?= htmlspecialchars($a['age']) ?></td>
<td><?= htmlspecialchars($a['health_status']) ?></td>
<td><?= htmlspecialchars($a['enclosure_name']) ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No animals assigned to you yet.</p>
<?php endif; ?>

<h2>Your Feeding Tasks</h2>
<?php if(mysqli_num_rows($feedings) > 0): ?>
<table>
<tr><th>Animal</th><th>Food</th><th>Quantity</th><th>Time</th></tr>
<?php while($f = mysqli_fetch_assoc($feedings)): ?>
<tr>
<td><?= htmlspecialchars($f['animal_name']) ?></td>
<td><?= htmlspecialchars($f['food_name']) ?></td>
<td><?= htmlspecialchars($f['quantity']) ?></td>
<td><?= htmlspecialchars($f['feeding_time']) ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No feeding tasks assigned to you yet.</p>
<?php endif; ?>

<h2>Medical Records (Your Vet Cases)</h2>
<?php if(mysqli_num_rows($medical) > 0): ?>
<table>
<tr><th>Animal</th><th>Diagnosis</th><th>Treatment</th><th>Date</th></tr>
<?php while($m = mysqli_fetch_assoc($medical)): ?>
<tr>
<td><?= htmlspecialchars($m['animal_name']) ?></td>
<td><?= htmlspecialchars($m['diagnosis']) ?></td>
<td><?= htmlspecialchars($m['treatment']) ?></td>
<td><?= htmlspecialchars($m['checkup_date']) ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No medical records assigned to you yet.</p>
<?php endif; ?>

</div>
</body>
</html>
