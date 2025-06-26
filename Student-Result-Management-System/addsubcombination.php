<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch subjects from the database
$subject_sql = "SELECT id, subject_name FROM subject";
$subject_result = $conn->query($subject_sql);
$subjects = [];
if ($subject_result->num_rows > 0) {
    while ($row = $subject_result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Fetch semesters from the database
$semester_sql = "
    SELECT semester.id, semester.sem_name, department.dname 
    FROM semester 
    JOIN department ON semester.dept_id = department.id
";
$semester_result = $conn->query($semester_sql);
$sem = [];
if ($semester_result->num_rows > 0) {
    while ($row = $semester_result->fetch_assoc()) {
        $sem[] = $row;
    }
}

// Fetch existing subject combinations
$subjectcomb_sql = "SELECT courseid, subid FROM subjectcomb";
$subjectcomb_result = $conn->query($subjectcomb_sql);
$existing_combinations = [];
if ($subjectcomb_result->num_rows > 0) {
    while ($row = $subjectcomb_result->fetch_assoc()) {
        $existing_combinations[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $semester_id = $_POST['semester'];
    $subject_id = $_POST['subject'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'student_test');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Validate semester and subject IDs
    $stmt_check_sem = $conn->prepare("SELECT id FROM semester WHERE id = ?");
    $stmt_check_sem->bind_param("i", $semester_id);
    $stmt_check_sem->execute();
    $stmt_check_sem->store_result();
    if ($stmt_check_sem->num_rows === 0) {
        die("Invalid semester selected.");
    }

    $stmt_check_sub = $conn->prepare("SELECT id FROM subject WHERE id = ?");
    $stmt_check_sub->bind_param("i", $subject_id);
    $stmt_check_sub->execute();
    $stmt_check_sub->store_result();
    if ($stmt_check_sub->num_rows === 0) {
        die("Invalid subject selected.");
    }

    $stmt_check_sem->close();
    $stmt_check_sub->close();

    // Prepare and bind statement
    $stmt = $conn->prepare("INSERT INTO subjectcomb (courseid, subid) VALUES (?, ?)");
    $stmt->bind_param("ii", $semester_id, $subject_id);

    // Execute and redirect on success
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: addsubcombination.php?success=1");
        exit();
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

$conn->close();
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
            <div class="alert alert-success">Subject combination added successfully!</div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <section id="subject">
            <h2 class="sub-heading">Subject Combination</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="semester">Semester:</label>
                    <select id="semester" name="semester" required>
                        <option value="">Select course and semester</option>
                        <?php foreach ($sem as $semester) : ?>
                            <option value="<?= htmlspecialchars($semester['id']); ?>">
                                <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <select id="subject" name="subject" required>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject) : ?>
                            <?php
                            // Check if the subject is already associated with a course
                            $subject_exists = false;
                            foreach ($existing_combinations as $combination) {
                                if ($combination['subid'] == $subject['id']) {
                                    $subject_exists = true;
                                    break;
                                }
                            }
                            if (!$subject_exists) {
                                ?>
                                <option value="<?= htmlspecialchars($subject['id']); ?>">
                                    <?= htmlspecialchars($subject['subject_name']); ?>
                                </option>
                            <?php } ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Submit</button>
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
