<?php
session_start();
$conn = mysqli_connect("localhost:3307","root","","Madagascar_db");
if (!$conn) die("Connection failed");

if(!isset($_SESSION['user_type']) || $_SESSION['user_type']!=='admin'){
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$feeding = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM Feeding_Schedule WHERE schedule_id=$id
"));
$animals = mysqli_query($conn, "SELECT animal_id,name FROM Animal");
$staff = mysqli_query($conn, "SELECT staff_id,name FROM Staff JOIN Role ON Staff.role_id=Role.role_id WHERE Role.role_name='Caretaker'");

if(isset($_POST['update'])){
    $animal = $_POST['animal'];
    $feeding_time = $_POST['feeding_time'];
    $food_name = $_POST['food_name'];
    $quantity = $_POST['quantity'];
    $feed_by = $_POST['feed_by'];

    mysqli_query($conn, "
        UPDATE Feeding_Schedule 
        SET animal_id='$animal', feeding_time='$feeding_time', food_name='$food_name', quantity='$quantity', feed_by='$feed_by'
        WHERE schedule_id=$id
    ");

    echo "<script>alert('Feeding schedule updated successfully!');window.location='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Feeding Schedule</title>
<style>
body {font-family: Arial, sans-serif; background: linear-gradient(135deg,#a2e3c4,#f9f871); margin:0; padding:30px;}
.container {max-width:600px; margin:auto; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
h2 {color:#2e8b57; text-align:center;}
form input, select {width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:8px;}
button {background:#2e8b57; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;}
button:hover {background:#3cb371;}
</style>
</head>
<body>
<div class="container">
<h2>Edit Feeding Schedule</h2>
<form method="POST">
<label>Animal:</label>
<select name="animal" required>
<?php while($a=mysqli_fetch_assoc($animals)): ?>
<option value="<?= $a['animal_id'] ?>" <?= $a['animal_id']==$feeding['animal_id']?'selected':'' ?>><?= $a['name'] ?></option>
<?php endwhile; ?>
</select>

<label>Feeding Time:</label>
<input type="time" name="feeding_time" value="<?= $feeding['feeding_time'] ?>" required>

<label>Food Name:</label>
<input type="text" name="food_name" value="<?= $feeding['food_name'] ?>" required>

<label>Quantity:</label>
<input type="text" name="quantity" value="<?= $feeding['quantity'] ?>" required>

<label>Feed By (Caretaker):</label>
<select name="feed_by" required>
<?php while($s=mysqli_fetch_assoc($staff)): ?>
<option value="<?= $s['staff_id'] ?>" <?= $s['staff_id']==$feeding['feed_by']?'selected':'' ?>><?= $s['name'] ?></option>
<?php endwhile; ?>
</select>

<button type="submit" name="update">Update Feeding Schedule</button>
</form>
</div>
</body>
</html>
