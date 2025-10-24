<?php
session_start();
$conn = mysqli_connect("localhost:3307","root","","Madagascar_db");
if (!$conn) die("Connection failed");

if(!isset($_SESSION['user_type']) || $_SESSION['user_type']!=='admin'){
    header("Location: login.php"); exit;
}

$id = $_GET['id'];
$animal = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Animal WHERE animal_id=$id"));

// Fetch enclosures & caretakers
$enclosures = mysqli_query($conn,"SELECT * FROM Enclosure");
$caretakers = mysqli_query($conn,"SELECT staff_id,name FROM Staff JOIN Role ON Staff.role_id=Role.role_id WHERE Role.role_name='Caretaker'");

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $species = $_POST['species'];
    $age = $_POST['age'];
    $health = $_POST['health'];
    $enclosure = $_POST['enclosure'];
    $caretaker = $_POST['caretaker'];

    $image = $animal['image'];
    if(!empty($_FILES['image']['name'])){
        $image = "uploads/".basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'],$image);
    }

    $query = "UPDATE Animal SET name='$name', species='$species', age='$age', health_status='$health', enclosure_id='$enclosure', caretaker_id='$caretaker', image='$image' WHERE animal_id=$id";
    mysqli_query($conn,$query);
    echo "<script>alert('Animal updated!');window.location='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html><head><title>Edit Animal</title></head>
<body>
<h2>Edit Animal</h2>
<form method="POST" enctype="multipart/form-data">
<input type="text" name="name" value="<?= $animal['name'] ?>" required>
<input type="text" name="species" value="<?= $animal['species'] ?>" required><br>
<input type="number" name="age" value="<?= $animal['age'] ?>" required>
<input type="text" name="health" value="<?= $animal['health_status'] ?>" required><br>
<select name="enclosure">
<?php while($e=mysqli_fetch_assoc($enclosures)): ?>
<option value="<?= $e['enclosure_id'] ?>" <?= $e['enclosure_id']==$animal['enclosure_id']?'selected':''?>><?= $e['name'] ?></option>
<?php endwhile; ?>
</select>
<select name="caretaker">
<?php while($c=mysqli_fetch_assoc($caretakers)): ?>
<option value="<?= $c['staff_id'] ?>" <?= $c['staff_id']==$animal['caretaker_id']?'selected':''?>><?= $c['name'] ?></option>
<?php endwhile; ?>
</select><br>
<label>Animal Image:</label>
<input type="file" name="image"><br>
<button type="submit" name="update">Update</button>
</form>
</body></html>
