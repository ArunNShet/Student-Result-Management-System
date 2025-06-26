<?php
// Fetch department data from the database
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch departments
$sql = "SELECT id, dname FROM department"; // Adjust table and column names as per your schema
$result = $conn->query($sql);

// Initialize an array to store departments
$dept = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dept[] = $row;
    }
}
$conn->close();

// Initialize error message
$errorMessage = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usn = $_POST['usn'];
    $studentName = $_POST['studentName'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $department = intval($_POST['department']); // Ensure department is an integer

    $conn = new mysqli('localhost', 'root', '', 'student_test');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check for duplicate USN
    $checkStmt = $conn->prepare("SELECT usn FROM student_info WHERE usn = ?");
    $checkStmt->bind_param("s", $usn);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $errorMessage = "The USN for this student has already been registered. Please use a unique USN to add a new student.";
    } else {
        // Proceed with insertion if no duplicate is found
        $stmt = $conn->prepare("INSERT INTO student_info (usn, sname, dob, email, address, dept_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $usn, $studentName, $dob, $email, $address, $department);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: addstudent.php?success=1");
            exit();
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkStmt->close();
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
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Student Registered successfully!</div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <section id="students">
            <h2 class="sub-heading">Student Details</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="usn">USN:</label>
                    <input type="text" id="usn" name="usn" placeholder="Enter USN" required>
                </div>
                <div class="form-group">
                    <label for="studentName">Student Name:</label>
                    <input type="text" id="studentName" name="studentName" placeholder="Enter student name" required>
                </div>
                <div class="form-group">
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Enter Student Email" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" placeholder="Enter Address" width="100%" required></textarea>
                </div>
                <div class="form-group">
                    <label for="department">Department:</label>
                    <select id="department" name="department" required>
                        <option value="">Select department</option>
                        <?php if (!empty($dept)) : ?>
                            <?php foreach ($dept as $department) : ?>
                                <option value="<?= htmlspecialchars($department['id']); ?>">
                                    <?= htmlspecialchars($department['dname']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit">Add Student</button>
            </form>
        </section>
    </div>
    <script>
    // Automatically hide the success message after 3 seconds
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
