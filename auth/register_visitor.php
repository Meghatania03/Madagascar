<?php
$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $visit_date = date('Y-m-d');

    $query = "INSERT INTO Visitor (name, phone, email, password, visit_date)
              VALUES ('$name', '$phone', '$email', '$password', '$visit_date')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Visitor registered successfully!'); window.location='login.php';</script>";
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
<title>Visitor Register - Madagascar Zoo</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #badc58, #6ab04c);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.container {
    background: #fff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    width: 400px;
    text-align: center;
}
h1 {
    color: #2d3436;
}
input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 10px;
    background-color: #218c74;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
}
button:hover {
    background-color: #1e6f5c;
}
a {
    color: #218c74;
    text-decoration: none;
    font-weight: 600;
}
</style>
</head>
<body>
<div class="container">
    <h1>🦋 Visitor Registration</h1>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="phone" placeholder="Phone (optional)">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
