<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sem_id'], $_POST['usn'], $_POST['subject_code'], $_POST['marks'])) {
    $semester_id = intval($_POST['sem_id']);
    $usn = $conn->real_escape_string($_POST['usn']);
    $subject_code = $_POST['subject_code'];
    $marks = $_POST['marks'];

    // Check if result already declared for the same USN and Semester
    $check_sql = "SELECT COUNT(*) as count FROM result WHERE usn = ? AND sem_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $usn, $semester_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();

    if ($check_row['count'] > 0) {
        $error_message = "Result for this USN and semester has already been declared!";
    } else {
        // Insert results into the result table
        for ($i = 0; $i < count($subject_code); $i++) {
            $subject_id = intval($subject_code[$i]);
            $mark = intval($marks[$i]);

            $insert_sql = "INSERT INTO result (usn, sem_id, subject_code, marks) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("siii", $usn, $semester_id, $subject_id, $mark);
            $stmt->execute();
        }

        header("Location: addresult.php?success=1");
        exit;
    }
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
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Result declared successfully!</div>
        <?php endif; ?>
        <h2 class="sub-heading">Declare Result</h2>
        <form method="POST">
            <div class="form-group">
                <label for="semester">Semester:</label>
                <select id="semester" name="sem_id" required onchange="fetchSubjects(this.value)">
                    <option value="">Select department and semester</option>
                    <?php foreach ($sem as $semester) : ?>
                        <option value="<?= htmlspecialchars($semester['id']); ?>">
                            <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
    <label for="usn">USN:</label>
    <div class="input-group">
        <input type="text" id="usn" name="usn" placeholder="Enter USN" required oninput="fetchStudentName(this.value)">
        <div class="input-group-addon" style="min-width: 200px;" id="studentName">Student Name</div>
    </div>
</div>
            <div class="form-group">
                <label for="subjects">Subjects and Marks:</label>
                <table id="subjects-list" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <button type="submit">Declare Result</button>
        </form>
    </div>
    <script>
        function fetchStudentName(usn) {
            if (!usn) {
                document.getElementById('studentName').innerText = '';
                return;
            }

            $.ajax({
                type: "POST",
                url: "fetch_student_name.php",
                data: { usn: usn },
                success: function(response) {
                    document.getElementById('studentName').innerText = response || "No student found with this USN.";
                },
                error: function(error) {
                    console.error('Error fetching student name:', error);
                }
            });
        }

        function fetchSubjects(semesterId) {
            if (!semesterId) {
                document.querySelector('#subjects-list tbody').innerHTML = '';
                return;
            }

            $.ajax({
                type: "POST",
                url: "fetch_data.php",
                data: { semester: semesterId },
                dataType: "json",
                success: function(response) {
                    const subjectsList = document.querySelector('#subjects-list tbody');
                    subjectsList.innerHTML = '';

                    if (response.subjects.length > 0) {
                        response.subjects.forEach(subject => {
                            subjectsList.innerHTML += `
                                <tr>
                                    <td>${subject.subject_name}</td>
                                    <td>
                                        <input type="number" name="marks[]" placeholder="Enter Marks out of 100" required>
                                        <input type="hidden" name="subject_code[]" value="${subject.subject_code}">
                                    </td>
                                </tr>`;
                        });
                    } else {
                        subjectsList.innerHTML = '<tr><td colspan="2">No subjects found for the selected semester.</td></tr>';
                    }
                },
                error: function(error) {
                    console.error('Error fetching data:', error);
                }
            });
        }

        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => alert.style.display = 'none');
        }, 5000);
    </script>
</body>

</html>
