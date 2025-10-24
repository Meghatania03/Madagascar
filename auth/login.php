<?php
session_start();

if (isset($_SESSION['user_type']) && isset($_SESSION['username'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: ../dashboard/{$_SESSION['user_type']}_dashboard.php");
    }
    exit;
} elseif (isset($_COOKIE['user_type']) && isset($_COOKIE['username'])) {
    $_SESSION['user_type'] = $_COOKIE['user_type'];
    $_SESSION['username'] = $_COOKIE['username'];

    if ($_SESSION['user_type'] === 'admin') {
        header("Location: ../admin/admin_dashboard.php");
    } else {
        header("Location: ../dashboards/{$_SESSION['user_type']}_dashboard.php");
    }
    exit;
}


// Database connection
$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['login'])) {
    $user_type = $_POST['user_type'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Choose table and fields based on role
    switch ($user_type) {
        case 'admin':
            $query = "SELECT * FROM admin WHERE email='$email'";
            break;
        case 'staff':
            $query = "SELECT * FROM staff WHERE email='$email'";
            break;
        case 'visitor':
            $query = "SELECT * FROM visitor WHERE email='$email'";
            break;
        default:
            $error = "Invalid user type selected.";
            $query = "";
    }

    if (!empty($query)) {
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if ($password === $user['password'])  {
                $_SESSION['username'] = $user['email'];
                $_SESSION['user_type'] = $user_type;

                // Remember me cookie
                if (!empty($_POST['remember'])) {
                    setcookie("username", $user['email'], time() + (86400 * 7), "/");
                    setcookie("user_type", $user_type, time() + (86400 * 7), "/");
                }


                // Assuming $user_type is either 'admin', 'staff', or 'visitor'
                if ($user_type === 'admin') {
                    header("Location: ../admin/admin_dashboard.php");
                    exit;
                } else {
                    header("Location: ../dashboards/{$user_type}_dashboard.php");
                    exit;
                }
            } else {
                $error = "Incorrect password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Madagascar Zoo Login</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, #6ab04c, #badc58);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #2d3436;
        }

        input,
        select {
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

        label {
            font-size: 13px;
        }

        .error {
            color: #e74c3c;
            background-color: #fdecea;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 10px;
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
        <h1>🦓 Madagascar Zoo Login</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <select name="user_type" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="visitor">Visitor</option>
            </select>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <label><input type="checkbox" name="remember"> Remember Me</label><br>
            <button type="submit" name="login">Login</button>
        </form>
        <p>New staff? <a href="register_staff.php">Register here</a></p>
    </div>
</body>

</html>