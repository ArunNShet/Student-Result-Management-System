<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Database connection
    $con = new mysqli('localhost', 'root', '', 'student_test');

    if ($con->connect_error) {
        die('Connection failed: ' . $con->connect_error);
    } else {
        // Check if the user exists
        $stmt = $con->prepare("SELECT * FROM user WHERE user_name = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, login successful
            $_SESSION['username'] = $username; // Store username in session
            $_SESSION['message'] = "Login successfully";

            // Redirect to the index.php page
            header("Location: index.php");
            exit();
        } else {
            // User doesn't exist or invalid credentials
            echo "<script>alert('Invalid username or password');</script>";
        }

        $stmt->close();
        $con->close();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
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
 </style>

<body>
    <div class="container">
        <div class="login-container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <h2>Student</h2>
                    <h3><center>Check Examination Result <a href="resultcheck.php">click here</a></center></h3>
                </div>
            </div>
        </div>
        <div class="login-container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <h2>Admin Login</h2>
                    <form method="POST">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button type="submit">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
