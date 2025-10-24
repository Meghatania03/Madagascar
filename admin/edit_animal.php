<?php
session_start();
$conn = mysqli_connect("localhost:3307","root","","Madagascar_db");
if (!$conn) die("Connection failed");

if(!isset($_SESSION['user_type']) || $_SESSION['user_type']!=='admin'){
    header("Location: ../auth/login.php"); exit;
}

$id = $_GET['id'];
$animal = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Animal WHERE animal_id=$id"));

// Fetch enclosures, caretakers, feeders
$enclosures = mysqli_query($conn,"SELECT * FROM Enclosure");
$caretakers = mysqli_query($conn,"SELECT staff_id,name FROM Staff JOIN Role ON Staff.role_id=Role.role_id WHERE Role.role_name='Caretaker'");
$feeders = mysqli_query($conn,"SELECT staff_id,name FROM Staff");

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $species = $_POST['species'];
    $age = $_POST['age'];
    $health = $_POST['health'];
    $enclosure = $_POST['enclosure'];
    $caretaker = $_POST['caretaker'];
    $feed_by = $_POST['feed_by'];

    $image = $animal['image'];
    if(!empty($_FILES['image']['name'])){
        $image = "../uploads/".basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'],$image);
    }

    $query = "UPDATE Animal SET name='$name', species='$species', age='$age', health_status='$health', enclosure_id='$enclosure', caretaker_id='$caretaker', feed_by='$feed_by', image='$image' WHERE animal_id=$id";
    mysqli_query($conn,$query);
    echo "<script>alert('Animal updated!');window.location='admin_dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Animal - Madagascar Zoo</title>
<style>
body { font-family: Arial,sans-serif; margin:0; padding:0; background: linear-gradient(135deg,#a2e3c4,#f9f871);}
.navbar {background:#2e8b57; color:white; padding:15px; font-size:24px; text-align:center;}
.container {max-width:600px; margin:30px auto; background:white; padding:20px; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
h2 {color:#2e8b57; text-align:center;}
form input, select {padding:10px;margin:5px;width:48%;border-radius:5px;border:1px solid #ccc;}
form button {background:#2e8b57;color:white;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;margin-top:10px;}
form button:hover {background:#3cb371;}
.logout {float:right;background:#e74c3c;color:white;border:none;padding:8px 15px;border-radius:8px;cursor:pointer;}
</style>
</head>
<body>
<div class="navbar">Edit Animal 🦁</div>
<div class="container">
<form method="POST" enctype="multipart/form-data">
<input type="text" name="name" value="<?= $animal['name'] ?>" placeholder="Name" required>
<input type="text" name="species" value="<?= $animal['species'] ?>" placeholder="Species" required><br>
<input type="number" name="age" value="<?= $animal['age'] ?>" placeholder="Age" required>
<input type="text" name="health" value="<?= $animal['health_status'] ?>" placeholder="Health Status" required><br>

<select name="enclosure" required>
<?php while($e=mysqli_fetch_assoc($enclosures)): ?>
<option value="<?= $e['enclosure_id'] ?>" <?= $e['enclosure_id']==$animal['enclosure_id']?'selected':'' ?>><?= $e['name'] ?> (<?= $e['type'] ?>)</option>
<?php endwhile; ?>
</select>

<select name="caretaker" required>
<?php while($c=mysqli_fetch_assoc($caretakers)): ?>
<option value="<?= $c['staff_id'] ?>" <?= $c['staff_id']==$animal['caretaker_id']?'selected':'' ?>><?= $c['name'] ?></option>
<?php endwhile; ?>
</select>

<select name="feed_by" required>
<?php while($f=mysqli_fetch_assoc($feeders)): ?>
<option value="<?= $f['staff_id'] ?>" <?= $f['staff_id']==$animal['feed_by']?'selected':'' ?>><?= $f['name'] ?></option>
<?php endwhile; ?>
</select><br>

<label>Animal Image:</label>
<input type="file" name="image"><br>
<button type="submit" name="update">Update Animal</button>
</form>
</div>
</body>
</html>
