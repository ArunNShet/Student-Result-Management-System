<?php
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
$semesters = [];
if ($semester_result->num_rows > 0) {
    while ($row = $semester_result->fetch_assoc()) {
        $semesters[] = $row;
    }
}

// Handle form submission for updating results
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sem_id'], $_POST['usn'], $_POST['subject_code'], $_POST['marks'])) {
    $semester_id = intval($_POST['sem_id']);
    $usn = $conn->real_escape_string($_POST['usn']);
    $subject_codes = $_POST['subject_code'];
    $marks = $_POST['marks'];

    // Begin transaction
    $conn->begin_transaction();
    try {
        for ($i = 0; $i < count($subject_codes); $i++) {
            $subject_code = intval($subject_codes[$i]);
            $mark = intval($marks[$i]);

            $update_sql = "UPDATE result SET marks = ? WHERE usn = ? AND sem_id = ? AND subject_code = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("isii", $mark, $usn, $semester_id, $subject_code);
            $stmt->execute();
        }

        // Commit the transaction
        $conn->commit();
        header("Location: manage_result.php?success=1");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        $errorMessage = "Error updating results: " . $e->getMessage();
    }
}

// Fetch results for only the selected semester (based on sem_id)
$semester_id = isset($_GET['sem_id']) ? intval($_GET['sem_id']) : 0; // Default to 0 if not set

// If a semester is selected, show its results
if ($semester_id > 0) {
    $result_sql = "SELECT result.usn, student_info.sname, department.dname, semester.sem_name
                  FROM result
                  JOIN student_info ON result.usn = student_info.usn
                  JOIN department ON student_info.dept_id = department.id
                  JOIN semester ON result.sem_id = semester.id
                  WHERE result.sem_id = ? 
                  GROUP BY result.usn"; // Group by USN to avoid duplicates
    $stmt = $conn->prepare($result_sql);
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result_result = $stmt->get_result();
} else {
    $result_result = null;
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

    <!-- Navigation Bar -->
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
        <!-- Display success or error messages -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Results updated successfully!</div>
        <?php elseif (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Select Semester Dropdown -->
        <h2 class="sub-heading">Manage Results</h2>
        <form method="GET">
            <div class="form-group">
                <label for="semester">Select Semester</label>
                <select id="semester" name="sem_id">
                    <option value="0">Select Semester</option>
                    <?php foreach ($semesters as $semester) : ?>
                        <option value="<?= $semester['id']; ?>" <?= ($semester_id == $semester['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Show Results</button>
        </form>

        <!-- Results Table -->
        <?php if ($semester_id > 0) : ?>
            <h3>Results for <?= htmlspecialchars($semesters[array_search($semester_id, array_column($semesters, 'id'))]['dname']) . ' - ' . htmlspecialchars($semesters[array_search($semester_id, array_column($semesters, 'id'))]['sem_name']); ?></h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>USN</th>
                        <th>Name</th>
                        <th>Semester</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_result && $result_result->num_rows > 0) {
                        while ($row = $result_result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['usn']); ?></td>
                                <td><?= htmlspecialchars($row['sname']); ?></td>
                                <td><?= htmlspecialchars($row['dname'] . ' - ' . $row['sem_name']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm edit-button"
                                            data-usn="<?= htmlspecialchars($row['usn']); ?>"
                                            data-sem-id="<?= htmlspecialchars($semester_id); ?>"
                                            data-sem-name="<?= htmlspecialchars($row['sem_name']); ?>"
                                            data-dept="<?= htmlspecialchars($row['dname']); ?>">
                                        Edit Results
                                    </button>
                                </td>
                            </tr>
                    <?php endwhile;
                    } else {
                        echo "<tr><td colspan='4'>No results found for this semester.</td></tr>";
                    } ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Edit Result Modal -->
    <div id="editResultModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Student Results</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="sem_id" id="edit-sem-id">
                        <input type="hidden" name="usn" id="edit-usn">

                        <!-- Dynamically populated subjects will be shown here -->
                        <div id="subjects-container"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Results</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // When the "Edit Results" button is clicked
            $('.edit-button').on('click', function () {
                const usn = $(this).data('usn');
                const semId = $(this).data('sem-id');
                const semName = $(this).data('sem-name');
                const dept = $(this).data('dept');

                // Set the values in the modal
                $('#edit-usn').val(usn);
                $('#edit-sem-id').val(semId);
                
                // Fetch the subjects and their current marks for the selected student and semester
                $.ajax({
                    url: 'get_subjects_and_marks.php', // Create this PHP file to return subjects & marks
                    method: 'GET',
                    data: { usn: usn, sem_id: semId },
                    success: function (data) {
                        $('#subjects-container').html(data); // Display subjects and their current marks in the modal
                    }
                });

                // Show the modal
                $('#editResultModal').modal('show');
            });
        });

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

<?php $conn->close(); ?>
