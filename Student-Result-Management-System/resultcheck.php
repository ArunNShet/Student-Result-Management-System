<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch semesters with department names
$semester_sql = "SELECT semester.id, department.dname, semester.sem_name 
                 FROM semester 
                 JOIN department ON semester.dept_id = department.id";
$semester_result = $conn->query($semester_sql);
$sem = [];
if ($semester_result->num_rows > 0) {
    while ($row = $semester_result->fetch_assoc()) {
        $sem[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usn = $_POST["usn"];
    $semester = $_POST["semester"];

    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    } else {
        // Check if the result exists for the given usn and semester
        $stmt = $conn->prepare("SELECT * FROM result WHERE usn = ? AND sem_id = ?");
        $stmt->bind_param("ss", $usn, $semester);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, login successful
            $_SESSION['usn'] = $usn; // Store usn in session
            $_SESSION['semester'] = $semester; // Correct session variable name

            // Redirect to the displayresult.php page
            header("Location: displayresult.php");
            exit();
        } else {
            // Invalid usn or semester
            echo "<script>alert('Student Details Not Found');</script>";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management System</title>
    <link rel="stylesheet" href="mystyle.css">
</head>
<style>
    body {
        background: linear-gradient(135deg, #4CAF50, #2196F3);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }
    .btn{
        background: #ff0000b3 !important;
    }
</style>

<body>
    <div class="container">
        <div class="login-container">
            <div class="row">
                <div class="col">
                    <h2>Student Result Management System</h2>
                    <section id="result">
                        <form method="POST">
                            <div class="form-group">
                                <label for="usn">USN:</label>
                                <input type="text" id="usn" name="usn" placeholder="Enter USN" required>
                            </div>
                            <div class="form-group">
                                <label for="semester">Semester:</label>
                                <select id="semester" name="semester" required>
                                    <option value="">Select Branch and semester</option>
                                    <?php foreach ($sem as $semester) : ?>
                                        <option value="<?= htmlspecialchars($semester['id']); ?>">
                                            <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit">Check Result</button>
                            <button type="button" class="btn" onclick="myFunction()">Back to Home</button>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
    <script>
        function myFunction() {
            window.location.href = "login.php";
        }
    </script>
</body>

</html>
