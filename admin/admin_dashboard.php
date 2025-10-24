<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$conn = mysqli_connect("localhost:3307", "root", "", "Madagascar_db");
if (!$conn) die("Connection failed: " . mysqli_connect_error());

/* ----------------------- FETCH DATA ----------------------- */

// Use Animal_View for cleaner animal info
$animals = mysqli_query($conn, "SELECT * FROM Animal_View");

// Staff and their animals
$staff = mysqli_query($conn, "
    SELECT s.staff_id, s.name, r.role_name, s.email, s.phone,
           GROUP_CONCAT(a.name SEPARATOR ', ') AS animals
    FROM Staff s
    JOIN Role r ON s.role_id=r.role_id
    LEFT JOIN Animal a ON a.caretaker_id=s.staff_id
    GROUP BY s.staff_id
");

// Visitors
$visitors = mysqli_query($conn, "SELECT * FROM Visitor");

// Enclosures & caretakers for forms
$enclosures = mysqli_query($conn, "SELECT * FROM Enclosure");
$caretakers = mysqli_query($conn, "
    SELECT staff_id,name FROM Staff 
    JOIN Role ON Staff.role_id=Role.role_id 
    WHERE Role.role_name='Caretaker'
");

// Staff list for JSON in JS
$staff_list = mysqli_query($conn, "SELECT staff_id,name FROM Staff");
$staff_json = [];
while ($row = mysqli_fetch_assoc($staff_list)) $staff_json[$row['staff_id']] = $row['name'];

// Zoo stats (aggregate + HAVING)
$stats_result = mysqli_query($conn, "
    SELECT e.name AS enclosure_name, e.type, COUNT(a.animal_id) AS total_animals, AVG(a.age) AS avg_age
    FROM Enclosure e
    LEFT JOIN Animal a ON e.enclosure_id=a.enclosure_id
    GROUP BY e.enclosure_id
    HAVING COUNT(a.animal_id) > 0
");

// Feeding schedule (joined)
$feeding_schedule = mysqli_query($conn, "
    SELECT f.schedule_id, a.name AS animal_name, f.feeding_time, f.food_name, f.quantity, s.name AS feed_by_name
    FROM Feeding_Schedule f
    JOIN Animal a ON f.animal_id=a.animal_id
    JOIN Staff s ON f.feed_by=s.staff_id
");

// Animals older than average (subquery example)
$older_animals = mysqli_query($conn, "
    SELECT name, species, age FROM Animal
    WHERE age > (SELECT AVG(age) FROM Animal)
");

/* ----------------------- ADD ANIMAL (with Transaction) ----------------------- */
if (isset($_POST['add_animal'])) {
    mysqli_begin_transaction($conn);
    try {
        $name = $_POST['name'];
        $species = $_POST['species'];
        $age = $_POST['age'];
        $health = $_POST['health'];
        $enclosure = $_POST['enclosure'];
        $caretaker = $_POST['caretaker'];

        $image = '';
        if (!empty($_FILES['image']['name'])) {
            // During upload:
            $image = "uploads/" . basename($_FILES['image']['name']);  // remove ../
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
        }

        $insert = "INSERT INTO Animal(name,species,age,health_status,enclosure_id,caretaker_id,image)
                 VALUES('$name','$species','$age','$health','$enclosure','$caretaker','$image')";
        mysqli_query($conn, $insert);
        $animal_id = mysqli_insert_id($conn);

        // Default feeding record (for trigger demo)
        $feed = "INSERT INTO Feeding_Schedule(animal_id,feeding_time,food_name,quantity,feed_by)
               VALUES('$animal_id','09:00:00','Default Food','1kg','$caretaker')";
        mysqli_query($conn, $feed);

        mysqli_commit($conn);
        echo "<script>alert('Animal & default feeding added successfully!');window.location='admin_dashboard.php';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Transaction failed! Rolled back.');</script>";
    }
}

/* ----------------------- ADD FEEDING TASK ----------------------- */
if (isset($_POST['add_feeding'])) {
    $animal = $_POST['animal'];
    $food = $_POST['food_name'];
    $quantity = $_POST['quantity'];
    $time = $_POST['feeding_time'];
    $feed_by = $_POST['feed_by'];

    $insert = "INSERT INTO Feeding_Schedule(animal_id,feeding_time,food_name,quantity,feed_by)
             VALUES('$animal','$time','$food','$quantity','$feed_by')";
    mysqli_query($conn, $insert);
    echo "<script>alert('Feeding task added!');window.location='admin_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Madagascar Zoo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #a2e3c4, #f9f871);
        }

        .navbar {
            background: #2e8b57;
            color: white;
            padding: 15px;
            font-size: 24px;
            text-align: center;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        h2 {
            color: #2e8b57;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #ccc;
        }

        th,
        td {
            padding: 8px;
            text-align: center;
        }

        th {
            background: #2e8b57;
            color: white;
        }

        form input,
        select {
            padding: 10px;
            margin: 5px;
            width: 48%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        form button {
            background: #2e8b57;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        form button:hover {
            background: #3cb371;
        }

        img {
            border-radius: 8px;
        }

        .logout {
            float: right;
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="navbar">
        Admin Dashboard - Madagascar Zoo 🦁
        <form method="POST" action="../auth/logout.php"><button class="logout">Logout</button></form>
    </div>

    <!-- ADD ANIMAL -->
    <div class="container">
        <h2>Add New Animal</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Animal Name" required>
            <input type="text" name="species" placeholder="Species" required><br>
            <input type="number" name="age" placeholder="Age" min="0" required>
            <input type="text" name="health" placeholder="Health Status" required><br>
            <select name="enclosure" required>
                <option value="">Select Enclosure</option>
                <?php while ($e = mysqli_fetch_assoc($enclosures)): ?>
                    <option value="<?= $e['enclosure_id'] ?>"><?= $e['name'] ?> (<?= $e['type'] ?>)</option>
                <?php endwhile; ?>
            </select>
            <select name="caretaker" required>
                <option value="">Select Caretaker</option>
                <?php mysqli_data_seek($caretakers, 0);
                while ($c = mysqli_fetch_assoc($caretakers)): ?>
                    <option value="<?= $c['staff_id'] ?>"><?= $c['name'] ?></option>
                <?php endwhile; ?>
            </select><br>
            <label>Animal Image:</label>
            <input type="file" name="image" accept="image/*"><br>
            <button type="submit" name="add_animal">Add Animal</button>
        </form>
    </div>

    <!-- ADD FEEDING TASK -->
    <div class="container">
        <h2>Add Feeding Task</h2>
        <form method="POST">
            <select name="animal" id="animalSelect" required>
                <option value="">Select Animal</option>
                <?php
                $animal_list = mysqli_query($conn, "SELECT animal_id,name,caretaker_id FROM Animal");
                while ($an = mysqli_fetch_assoc($animal_list)) {
                    echo "<option value='{$an['animal_id']}' data-caretaker='{$an['caretaker_id']}'>{$an['name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="food_name" placeholder="Food Name" required>
            <input type="text" name="quantity" placeholder="Quantity" required>
            <input type="time" name="feeding_time" required>
            <select name="feed_by" id="feedBySelect" required>
                <option value="">Select Feeder (Caretaker)</option>
            </select>
            <button type="submit" name="add_feeding">Add Feeding Task</button>
        </form>
    </div>

    <!-- ALL ANIMALS -->
    <div class="container">
        <h2>All Animals</h2>
        <table>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Species</th>
                <th>Age</th>
                <th>Health</th>
                <th>Enclosure</th>
                <th>Caretaker</th>
                <th>Actions</th>
            </tr>
            <?php while ($a = mysqli_fetch_assoc($animals)): ?>
                <tr>
                    <td><?php if ($a['image']): ?><img src="../<?= $a['image'] ?>" width="80" height="80"><?php else: ?>No Image<?php endif; ?></td>
                    <td><?= $a['animal_name'] ?></td>
                    <td><?= $a['species'] ?></td>
                    <td><?= $a['age'] ?></td>
                    <td><?= $a['health_status'] ?></td>
                    <td><?= $a['enclosure_name'] ?></td>
                    <td><?= $a['caretaker_name'] ?></td>
                    <td>
                        <a href="edit_animal.php?id=<?= $a['animal_id'] ?>">Edit</a> |
                        <a href="delete_animal.php?id=<?= $a['animal_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- Feeding Schedule -->
    <div class="container">
        <h2>Feeding Schedule</h2>
        <table>
            <tr>
                <th>Animal</th>
                <th>Feeding Time</th>
                <th>Food</th>
                <th>Quantity</th>
                <th>Feed By</th>
                <th>Actions</th>
            </tr>
            <?php while ($f = mysqli_fetch_assoc($feeding_schedule)): ?>
                <tr>
                    <td><?= $f['animal_name'] ?></td>
                    <td><?= $f['feeding_time'] ?></td>
                    <td><?= $f['food_name'] ?></td>
                    <td><?= $f['quantity'] ?></td>
                    <td><?= $f['feed_by_name'] ?></td>
                    <td>
                        <a href="edit_feeding.php?id=<?= $f['schedule_id'] ?>">Edit</a> |
                        <a href="delete_feeding.php?id=<?= $f['schedule_id'] ?>" onclick="return confirm('Are you sure you want to delete this feeding schedule?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>



    <!-- STAFF MANAGEMENT -->
    <div class="container">
        <h2>Staff Management</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Animals</th>
                <th>Actions</th>
            </tr>
            <?php while ($s = mysqli_fetch_assoc($staff)): ?>
                <tr>
                    <td><?= $s['name'] ?></td>
                    <td><?= $s['role_name'] ?></td>
                    <td><?= $s['email'] ?></td>
                    <td><?= $s['phone'] ?></td>
                    <td><?= $s['animals'] ? $s['animals'] : 'None' ?></td>
                    <td>
                        <a href="edit_staff.php?id=<?= $s['staff_id'] ?>">Edit</a> |
                        <a href="delete_staff.php?id=<?= $s['staff_id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- VISITORS -->
    <div class="container">
        <h2>Visitors</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Visit Date</th>
            </tr>
            <?php while ($v = mysqli_fetch_assoc($visitors)): ?>
                <tr>
                    <td><?= $v['name'] ?></td>
                    <td><?= $v['email'] ?></td>
                    <td><?= $v['phone'] ?></td>
                    <td><?= $v['visit_date'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- ZOO STATS -->
    <div class="container">
        <h2>Zoo Statistics</h2>
        <table>
            <tr>
                <th>Enclosure</th>
                <th>Type</th>
                <th>Total Animals</th>
                <th>Average Age</th>
            </tr>
            <?php while ($s = mysqli_fetch_assoc($stats_result)): ?>
                <tr>
                    <td><?= $s['enclosure_name'] ?></td>
                    <td><?= $s['type'] ?></td>
                    <td><?= $s['total_animals'] ?></td>
                    <td><?= number_format($s['avg_age'], 1) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- SUBQUERY DISPLAY -->
    <div class="container">
        <h2>Animals Older Than Average Age</h2>
        <table>
            <tr>
                <th>Name</th>
                <th>Species</th>
                <th>Age</th>
            </tr>
            <?php while ($o = mysqli_fetch_assoc($older_animals)): ?>
                <tr>
                    <td><?= $o['name'] ?></td>
                    <td><?= $o['species'] ?></td>
                    <td><?= $o['age'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        const animalSelect = document.getElementById('animalSelect');
        const feedBySelect = document.getElementById('feedBySelect');
        const staffData = <?= json_encode($staff_json) ?>;

        animalSelect.addEventListener('change', function() {
            const caretakerId = this.options[this.selectedIndex].dataset.caretaker;
            feedBySelect.innerHTML = '';
            if (caretakerId && staffData[caretakerId]) {
                const opt = document.createElement('option');
                opt.value = caretakerId;
                opt.textContent = staffData[caretakerId];
                feedBySelect.appendChild(opt);
            } else {
                feedBySelect.innerHTML = '<option value=\"\">No caretaker assigned</option>';
            }
        });
    </script>

</body>

</html>