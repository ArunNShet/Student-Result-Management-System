<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize messages
$errorMessage = "";
$successMessage = "";

// Handle form submission for add and edit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine if it's an edit or add based on 'assignment_id' field
    $assignment_id = isset($_POST['assignment_id']) ? intval($_POST['assignment_id']) : null;
    $usn = $_POST['usn'] ?? '';
    $semester_id = $_POST['semester'] ?? '';

    if (!empty($usn) && !empty($semester_id)) {
        if ($assignment_id) {
            // Update existing assignment
            $stmt = $conn->prepare("UPDATE studentsem SET usn = ?, semid = ? WHERE id = ?");
            $stmt->bind_param("sii", $usn, $semester_id, $assignment_id);
            if ($stmt->execute()) {
                $successMessage = "Assignment updated successfully!";
            } else {
                $errorMessage = "Error updating assignment: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Insert new assignment
            // Check if the combination already exists
            $check_stmt = $conn->prepare("SELECT id FROM studentsem WHERE usn = ? AND semid = ?");
            $check_stmt->bind_param("si", $usn, $semester_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $errorMessage = "This student is already assigned to the selected semester.";
            } else {
                $stmt = $conn->prepare("INSERT INTO studentsem (usn, semid) VALUES (?, ?)");
                $stmt->bind_param("si", $usn, $semester_id);
                if ($stmt->execute()) {
                    $successMessage = "Semester Assignment updated successfully!";
                } else {
                    $errorMessage = "Error adding assignment: " . $stmt->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        }
    } else {
        $errorMessage = "Please fill in all required fields.";
    }
}

// Fetch assignments
$assignments_sql = "SELECT studentsem.id, studentsem.usn, student_info.sname, semester.id AS semid, semester.sem_name, department.dname
                   FROM studentsem
                   INNER JOIN student_info ON studentsem.usn = student_info.usn
                   INNER JOIN semester ON studentsem.semid = semester.id
                   INNER JOIN department ON semester.dept_id = department.id";
$assignments_result = $conn->query($assignments_sql);
$assignments = $assignments_result->fetch_all(MYSQLI_ASSOC);

// Fetch all students
$students_sql = "SELECT usn, sname FROM student_info";
$students_result = $conn->query($students_sql);
$students = $students_result->fetch_all(MYSQLI_ASSOC);

// Fetch all semesters
$semesters_sql = "SELECT semester.id, department.dname, semester.sem_name 
                  FROM semester 
                  INNER JOIN department ON semester.dept_id = department.id";
$semesters_result = $conn->query($semesters_sql);
$semesters = $semesters_result->fetch_all(MYSQLI_ASSOC);

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management System</title>
    <link rel="stylesheet" href="mystyle.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
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
        <!-- Display Success Message -->
        <?php if (!empty($successMessage)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <!-- Display Error Message -->
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Assignments Table -->
        <h2 class="sub-heading">Student-Semester Assignments</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>USN</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($assignments)) : ?>
                    <?php foreach ($assignments as $assignment) : ?>
                        <tr>
                            <td><?= htmlspecialchars($assignment['usn']); ?></td>
                            <td><?= htmlspecialchars($assignment['sname']); ?></td>
                            <td><?= htmlspecialchars($assignment['dname']); ?></td>
                            <td><?= htmlspecialchars($assignment['sem_name']); ?></td>
                            <td>
                                <!-- Edit Button with Data Attributes -->
                                <button class="btn btn-warning btn-sm edit-button" 
                                        data-id="<?= htmlspecialchars($assignment['id']); ?>" 
                                        data-usn="<?= htmlspecialchars($assignment['usn']); ?>" 
                                        data-semester="<?= htmlspecialchars($assignment['semid']); ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No assignments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Assignment Modal -->
    <div id="editModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal Content-->
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Assignment</h4>
                    </div>
                    <div class="modal-body">
                        <!-- Hidden Field to Store Assignment ID -->
                        <input type="hidden" id="assignment_id" name="assignment_id">
                        <div class="form-group">
                            <label for="edit-usn">USN:</label>
                            <select id="edit-usn" name="usn" class="form-control" required>
                                <option value="">Select USN</option>
                                <?php foreach ($students as $student) : ?>
                                    <option value="<?= htmlspecialchars($student['usn']); ?>">
                                        <?= htmlspecialchars($student['usn'] . ' - ' . $student['sname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-semester">Semester:</label>
                            <select id="edit-semester" name="semester" class="form-control" required>
                                <option value="">Select Semester</option>
                                <?php foreach ($semesters as $semester) : ?>
                                    <option value="<?= htmlspecialchars($semester['id']); ?>">
                                        <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <!-- Close Modal Button -->
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript to Handle Edit Button Clicks -->
    <script>
        $(document).ready(function () {
            $('.edit-button').on('click', function () {
                // Get data attributes from the clicked button
                const id = $(this).data('id');
                const usn = $(this).data('usn');
                const semester = $(this).data('semester');

                // Populate the modal form fields with the current assignment data
                $('#assignment_id').val(id);
                $('#edit-usn').val(usn);
                $('#edit-semester').val(semester);

                // Show the modal
                $('#editModal').modal('show');
            });

            // Optional: Automatically hide alerts after a few seconds
            setTimeout(function() {
                $(".alert").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove(); 
                });
            }, 4000);
        });
        
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
