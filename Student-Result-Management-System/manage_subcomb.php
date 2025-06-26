<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch existing subject combinations
$subjectcomb_sql = "
    SELECT 
        subjectcomb.id as comb_id, 
        semester.sem_name, 
        department.dname, 
        subject.subject_name, 
        semester.id as semester_id, 
        subject.id as subject_id 
    FROM subjectcomb 
    JOIN semester ON subjectcomb.courseid = semester.id 
    JOIN department ON semester.dept_id = department.id 
    JOIN subject ON subjectcomb.subid = subject.id";
$subjectcomb_result = $conn->query($subjectcomb_sql);

$combinations = [];
if ($subjectcomb_result->num_rows > 0) {
    while ($row = $subjectcomb_result->fetch_assoc()) {
        $combinations[] = $row;
    }
}

// Fetch semesters and subjects for editing
$semester_sql = "SELECT semester.id, semester.sem_name, department.dname FROM semester JOIN department ON semester.dept_id = department.id";
$subject_sql = "SELECT id, subject_name FROM subject";

$semesters = $conn->query($semester_sql)->fetch_all(MYSQLI_ASSOC);
$subjects = $conn->query($subject_sql)->fetch_all(MYSQLI_ASSOC);

// Handle edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $comb_id = $_POST['edit_id'];
    $semester_id = $_POST['semester'];
    $subject_id = $_POST['subject'];

    $update_sql = "UPDATE subjectcomb SET courseid = ?, subid = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("iii", $semester_id, $subject_id, $comb_id);

    if ($stmt->execute()) {
        header("Location: manage_subcomb.php?success=1");
        exit();
    } else {
        $errorMessage = "Error updating record: " . $stmt->error;
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
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Subject combination updated successfully!</div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <h2 class="sub-heading">Manage Subject Combinations</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course and Semester</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($combinations)) : ?>
                    <?php foreach ($combinations as $index => $comb) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>
                            <td><?= htmlspecialchars($comb['dname'] . ' - ' . $comb['sem_name']); ?></td>
                            <td><?= htmlspecialchars($comb['subject_name']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-button" 
                                    data-id="<?= $comb['comb_id']; ?>" 
                                    data-semester="<?= $comb['semester_id']; ?>" 
                                    data-subject="<?= $comb['subject_id']; ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No subject combinations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Edit Modal -->
        <div id="editModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Edit Subject Combination</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-id" name="edit_id">
                            <div class="form-group">
                                <label for="semester">Semester:</label>
                                <select id="edit-semester" name="semester" class="form-control" required>
                                    <option value="">Select Semester</option>
                                    <?php foreach ($semesters as $semester) : ?>
                                        <option value="<?= htmlspecialchars($semester['id']); ?>">
                                            <?= htmlspecialchars($semester['dname'] . ' - ' . $semester['sem_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <select id="edit-subject" name="subject" class="form-control" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects as $subject) : ?>
                                        <option value="<?= htmlspecialchars($subject['id']); ?>">
                                            <?= htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $('.edit-button').on('click', function () {
                const id = $(this).data('id');
                const semester_id = $(this).data('semester');
                const subject_id = $(this).data('subject');

                $('#edit-id').val(id);
                $('#edit-semester').val(semester_id);
                $('#edit-subject').val(subject_id);

                $('#editModal').modal('show');
            });
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
