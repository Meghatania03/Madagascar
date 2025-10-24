<?php
session_start();
$conn = mysqli_connect("localhost:3307","root","","Madagascar_db");
if (!$conn) die("Connection failed");

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

$id = $_GET['id'];
$staff = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM Staff WHERE staff_id=$id"));
$roles = mysqli_query($conn,"SELECT * FROM Role");

// Fetch all animals
$all_animals = mysqli_query($conn,"SELECT * FROM Animal");

// Fetch animals currently assigned to this staff
$current_animals_res = mysqli_query($conn,"SELECT animal_id FROM Animal WHERE caretaker_id=$id");
$current_animals = [];
while($row = mysqli_fetch_assoc($current_animals_res)) {
    $current_animals[] = $row['animal_id'];
}

if(isset($_POST['update'])){
    $role = $_POST['role'];
    mysqli_query($conn,"UPDATE Staff SET role_id='$role' WHERE staff_id=$id");

    // Update animal assignments
    // First, clear previous assignments for this staff
    mysqli_query($conn,"UPDATE Animal SET caretaker_id=NULL WHERE caretaker_id=$id");

    // Assign selected animals
    if(!empty($_POST['animals'])){
        $selected_animals = $_POST['animals']; // array of animal_ids
        foreach($selected_animals as $animal_id){
            mysqli_query($conn,"UPDATE Animal SET caretaker_id=$id WHERE animal_id=$animal_id");
        }
    }

    echo "<script>alert('Staff role and animal assignments updated');window.location='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Staff Role & Animals</title>
    <style>
        body { font-family: Arial, sans-serif; padding:20px; background: #f0f8ff; }
        h2 { color:#2e8b57; }
        form select { padding:8px; margin:5px 0; width:300px; }
        form button { padding:10px 20px; background:#2e8b57; color:white; border:none; border-radius:5px; cursor:pointer; }
        form button:hover { background:#3cb371; }
        label { font-weight:bold; }
    </style>
</head>
<body>
<h2>Edit Staff Role & Animals</h2>

<p><strong>Name:</strong> <?= $staff['name'] ?></p>
<p><strong>Email:</strong> <?= $staff['email'] ?></p>

<form method="POST">
    <label>Select Role:</label><br>
    <select name="role" required>
        <?php while($r = mysqli_fetch_assoc($roles)): ?>
            <option value="<?= $r['role_id'] ?>" <?= $r['role_id'] == $staff['role_id'] ? 'selected' : '' ?>>
                <?= $r['role_name'] ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Assign Animals:</label><br>
    <select name="animals[]" multiple size="8">
        <?php while($a = mysqli_fetch_assoc($all_animals)): ?>
            <option value="<?= $a['animal_id'] ?>" <?= in_array($a['animal_id'], $current_animals) ? 'selected' : '' ?>>
                <?= $a['name'] ?> (<?= $a['species'] ?>)
            </option>
        <?php endwhile; ?>
    </select><br>
    <small>Hold Ctrl (or Cmd) to select multiple animals</small><br><br>

    <button type="submit" name="update">Update Role & Animals</button>
</form>

</body>
</html>
