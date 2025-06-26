<?php
$errorMessage = ""; // Initialize the variable to avoid undefined variable error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $subjectname = $_POST['subjectname'];
    $subjectcode = $_POST['subjectcode'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'student_test');

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the subject code already exists
    $check_query = "SELECT COUNT(*) AS count FROM subject WHERE subject_code = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("s", $subjectcode);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row_check = $result_check->fetch_assoc();

    if ($row_check['count'] > 0) {
        $errorMessage = "This Subject Code is already added.";
    } else {
        // Prepare and bind statement
        $stmt = $conn->prepare("INSERT INTO subject (subject_name, subject_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $subjectname, $subjectcode);

        // Execute and check if successful
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: addsubject.php?success=1");
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        // Close statement
        $stmt->close();
    }

    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management System</title>
    <link rel="stylesheet" href="mystyle.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <header>
        <h1>Student Result Management System</h1>
    </header>
    <nav class="navbar navbar-inverse">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="myNavbar">
                <ul class="nav navbar-nav">
                    <li><a href="index.php">Home</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown">Department<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="adddept.php">Add Branch</a></li>
                            <li><a href="manage_dept.php">Manage Branch</a></li>
                            <li><a href="addsem.php">Add Semester</a></li>
                            <li><a href="manage_sem.php">Manage Semester</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown">Subject<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="addsubject.php">Add Subject</a></li>
                            <li><a href="manage_subject.php">Manage Subject</a></li>
                            <li><a href="addsubcombination.php">Add Subject Combination</a></li>
                            <li><a href="Manage_subcomb.php">Manage Subject Combination</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown">Student Details<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="addstudent.php">Add Student</a></li>
                            <li><a href="manage_stud.php">Manage Student</a></li>
                            <li><a href="addstudsem.php">Add Student semester</a></li>
                            <li><a href="manage_studsem.php">Manage Student Sem</a></li>

                        </ul>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown">Result<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="addresult.php">Declare Result</a></li>
                            <li><a href="manage_result.php">Manage Result</a></li>

                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="login.php"><span class="glyphicon glyphicon-log-out"></span> Log-out</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Display success or error message -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Subject added successfully!</div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section id="subject">
            <h2 class="sub-heading">Subject Details</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="subjectname">Subject Name:</label>
                    <input type="text" id="subjectname" name="subjectname" placeholder="Enter Subject Name" required>
                </div>
                <div class="form-group">
                    <label for="subjectcode">Subject Code:</label>
                    <input type="text" id="subjectcode" name="subjectcode" placeholder="Enter Subject Code" required>
                </div>
                <button type="submit">Add Subject</button>
            </form>
        </section>
    </div>
    <script>
        // Automatically hide the success or error message after 3 seconds
        window.addEventListener('load', () => {
            ['alert-success', 'alert-danger'].forEach(alertClass => {
                const alertElement = document.querySelector('.' + alertClass);
                if (alertElement) {
                    setTimeout(() => {
                        alertElement.style.display = 'none';
                    }, 5000);
                }
            });
        });
    </script>

</body>

</html>
