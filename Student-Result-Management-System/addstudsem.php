<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students from the database
$student_sql = "SELECT sid, usn FROM student_info";
$student_result = $conn->query($student_sql);
$students = [];
if ($student_result->num_rows > 0) {
    while ($row = $student_result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch semesters and department names from the database
$semester_sql = "SELECT semester.id, department.dname, semester.sem_name 
                 FROM semester 
                 INNER JOIN department ON semester.dept_id = department.id";
$semester_result = $conn->query($semester_sql);
$sem = [];
if ($semester_result->num_rows > 0) {
    while ($row = $semester_result->fetch_assoc()) {
        $sem[] = $row;
    }
}

$conn->close();

// Initialize error and success messages
$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usn = $_POST['usn'] ?? '';
    $semester_id = $_POST['semester'] ?? '';

    if (!empty($usn) && !empty($semester_id)) {
        // Reconnect for validation and insertion
        $conn = new mysqli('localhost', 'root', '', 'student_test');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the USN and semester combination already exists
        $check_sql = "SELECT * FROM studentsem WHERE usn = ? AND semid = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $usn, $semester_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // If combination exists, display error message
            $errorMessage = "This USN is already assigned to this semester.";
        } else {
            // Prepare and bind statement for insertion
            $stmt = $conn->prepare("INSERT INTO studentsem (usn, semid) VALUES (?, ?)");
            $stmt->bind_param("si", $usn, $semester_id);

            // Execute and set success message on success
            if ($stmt->execute()) {
                $successMessage = "Student semester assigned successfully!";
            } else {
                $errorMessage = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check_stmt->close();
        $conn->close();
    } else {
        $errorMessage = "Please fill in all required fields.";
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
        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section id="subject">
            <h2 class="sub-heading">Add Student Semester</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="usn">USN:</label>
                    <select id="usn" name="usn" required>
                        <option value="">Select USN</option>
                        <?php foreach ($students as $student_info) : ?>
                            <option value="<?= htmlspecialchars($student_info['usn']); ?>">
                                <?= htmlspecialchars($student_info['usn']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="semester">Semester:</label>
                    <select id="semester" name="semester" required>
                        <option value="">Select department and semester</option>
                        <?php foreach ($sem as $semester) : ?>
                            <option value="<?= htmlspecialchars($semester['id']); ?>">
                                <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Submit</button>
            </form>
        </section>
    </div>
    <script>
        // Automatically hide success and danger messages
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
