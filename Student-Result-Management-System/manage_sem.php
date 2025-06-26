<?php
$conn = new mysqli('localhost', 'root', '', 'student_test');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch semesters and their department names
$sql = "
    SELECT semester.id AS sem_id, semester.sem_name, department.dname, department.id AS dept_id
    FROM semester
    JOIN department ON semester.dept_id = department.id";
$result = $conn->query($sql);

$semesters = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $semesters[] = $row;
    }
}

// Fetch department list for editing modal
$sql = "SELECT id, dname FROM department";
$deptResult = $conn->query($sql);
$departments = [];
if ($deptResult->num_rows > 0) {
    while ($row = $deptResult->fetch_assoc()) {
        $departments[] = $row;
    }
}

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $semId = $_POST['sem_id'];
    $semName = $_POST['sem_name'];
    $deptId = $_POST['dept_id'];

    $stmt = $conn->prepare("UPDATE semester SET sem_name = ?, dept_id = ? WHERE id = ?");
    $stmt->bind_param("sii", $semName, $deptId, $semId);

    if ($stmt->execute()) {
        header("Location: manage_sem.php?success=1");
        exit();
    } else {
        $errorMessage = "Error updating semester: " . $stmt->error;
    }

    $stmt->close();
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

        <!-- Success or Error Message -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Semester updated successfully!</div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <h2 class="sub-heading">Manage Semesters</h2>
        <!-- Semester Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Semester ID</th>
                    <th>Semester Name</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($semesters)) : ?>
                    <?php foreach ($semesters as $semester) : ?>
                        <tr>
                            <td><?= htmlspecialchars($semester['sem_id']); ?></td>
                            <td><?= htmlspecialchars($semester['sem_name']); ?></td>
                            <td><?= htmlspecialchars($semester['dname']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-button" 
                                        data-sem-id="<?= htmlspecialchars($semester['sem_id']); ?>"
                                        data-sem-name="<?= htmlspecialchars($semester['sem_name']); ?>"
                                        data-dept-id="<?= htmlspecialchars($semester['dept_id']); ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No semesters found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Semester</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit-sem-id" name="sem_id">
                        <div class="form-group">
                            <label for="edit-sem-name">Semester Name:</label>
                            <input type="text" id="edit-sem-name" name="sem_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-dept-id">Department:</label>
                            <select id="edit-dept-id" name="dept_id" class="form-control" required>
                                <?php foreach ($departments as $department) : ?>
                                    <option value="<?= $department['id']; ?>"><?= htmlspecialchars($department['dname']); ?></option>
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

    <script>
        $(document).ready(function () {
            $('.edit-button').on('click', function () {
                const semId = $(this).data('sem-id');
                const semName = $(this).data('sem-name');
                const deptId = $(this).data('dept-id');

                $('#edit-sem-id').val(semId);
                $('#edit-sem-name').val(semName);
                $('#edit-dept-id').val(deptId);

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
