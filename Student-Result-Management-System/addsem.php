<?php
// Fetch departments
$conn = new mysqli('localhost', 'root', '', 'student_test');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, dname FROM department";
$result = $conn->query($sql);

$dept = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dept[] = $row;
    }
}
$conn->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $coursename = $_POST['coursename'];
    $semname = $_POST['sem'];

    $conn = new mysqli('localhost', 'root', '', 'student_test');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT INTO semester (dept_id, sem_name) VALUES (?, ?)");
    $stmt->bind_param("is", $coursename, $semname);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: addsem.php?success=1");
        exit();
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
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
            <div class="alert alert-success">Semester added successfully!</div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section>
            <h2 class="sub-heading">Semester Details</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="coursename">Course Name:</label>
                    <select id="coursename" name="coursename" required>
                        <option value="">Select Course Name</option>
                        <?php foreach ($dept as $department) : ?>
                            <option value="<?= htmlspecialchars($department['id']); ?>">
                                <?= htmlspecialchars($department['dname']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sem">Enter Semester:</label>
                    <input type="text" id="sem" name="sem" placeholder="like 1-semester, 2-semester..." required>
                </div>
                <button type="submit">Add Semester</button>
            </form>
        </section>
    </div>
    <script>
    // Automatically hide the success message after 3 seconds
    window.addEventListener('load', () => {
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 5000); // 3000ms = 3 seconds
        }
    });
</script>

</body>

</html>
