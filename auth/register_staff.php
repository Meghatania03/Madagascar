<?php
$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch roles
$roles = mysqli_query($conn, "SELECT * FROM Role");

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $role_id = $_POST['role_id'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO Staff (name, role_id, phone, email, password)
              VALUES ('$name', '$role_id', '$phone', '$email', '$password')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Staff registered successfully!'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Register - Madagascar Zoo</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #badc58, #6ab04c);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.container {
    background: rgba(255, 255, 255, 0.95);
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    width: 400px;
    text-align: center;
}
h1 {
    margin-bottom: 20px;
    color: #2d3436;
}
input, select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
}
button {
    background-color: #218c74;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    width: 100%;
    font-size: 16px;
    margin-top: 10px;
}
button:hover {
    background-color: #1e6f5c;
}
a {
    color: #218c74;
    text-decoration: none;
    font-weight: 600;
}
a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>
<div class="container">
    <h1>🐘 Staff Registration</h1>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <select name="role_id" required>
            <option value="">-- Select Role --</option>
            <?php while($row = mysqli_fetch_assoc($roles)) { ?>
                <option value="<?= $row['role_id'] ?>"><?= $row['role_name'] ?></option>
            <?php } ?>
        </select>
        <input type="text" name="phone" placeholder="Phone (optional)">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>
</body>
</html>
