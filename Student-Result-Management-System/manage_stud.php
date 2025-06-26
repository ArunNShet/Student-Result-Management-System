<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'student_test');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMessage = "";
$successMessage = "";

// Fetch departments
$deptResult = $conn->query("SELECT id, dname FROM department");
$departments = $deptResult->fetch_all(MYSQLI_ASSOC);

// Fetch student data
$sql = "SELECT student_info.usn, student_info.sname, student_info.dob, student_info.email, student_info.address, 
               department.dname, student_info.dept_id 
        FROM student_info 
        JOIN department ON student_info.dept_id = department.id";
$result = $conn->query($sql);
$students = $result->fetch_all(MYSQLI_ASSOC);

// Process the form submission for editing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usn = $_POST['usn'];
    $studentName = $_POST['studentName'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $department = intval($_POST['department']);

    // Update the student details
    $stmt = $conn->prepare("UPDATE student_info SET sname = ?, dob = ?, email = ?, address = ?, dept_id = ? WHERE usn = ?");
    $stmt->bind_param("ssssis", $studentName, $dob, $email, $address, $department, $usn);

    if ($stmt->execute()) {
        $successMessage = "Student details updated successfully!";
    } else {
        $errorMessage = "Error updating student details: " . $stmt->error;
    }
    $stmt->close();

    // Refresh the student data after update
    $result = $conn->query($sql);
    $students = $result->fetch_all(MYSQLI_ASSOC);
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
        <?php elseif (!empty($errorMessage)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <h2 class="sub-heading">Manage Student Details</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>USN</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)) : ?>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td><?= htmlspecialchars($student['usn']); ?></td>
                            <td><?= htmlspecialchars($student['sname']); ?></td>
                            <td><?= htmlspecialchars($student['dname']); ?></td>
                            <td><?= htmlspecialchars($student['email']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-button" 
                                        data-usn="<?= htmlspecialchars($student['usn']); ?>" 
                                        data-name="<?= htmlspecialchars($student['sname']); ?>" 
                                        data-dob="<?= htmlspecialchars($student['dob']); ?>" 
                                        data-email="<?= htmlspecialchars($student['email']); ?>" 
                                        data-address="<?= htmlspecialchars($student['address']); ?>" 
                                        data-dept="<?= htmlspecialchars($student['dept_id']); ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No students found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="editModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Edit Student</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-usn" name="usn">
                            <div class="form-group">
                                <label for="studentName">Student Name:</label>
                                <input type="text" id="edit-studentName" name="studentName" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="dob">Date of Birth:</label>
                                <input type="date" id="edit-dob" name="dob" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="edit-email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address:</label>
                                <textarea id="edit-address" name="address" class="form-control" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="department">Department:</label>
                                <select id="edit-department" name="department" class="form-control" required>
                                    <?php foreach ($departments as $dept) : ?>
                                        <option value="<?= $dept['id']; ?>"><?= htmlspecialchars($dept['dname']); ?></option>
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
                const usn = $(this).data('usn');
                const name = $(this).data('name');
                const dob = $(this).data('dob');
                const email = $(this).data('email');
                const address = $(this).data('address');
                const dept = $(this).data('dept');

                $('#edit-usn').val(usn);
                $('#edit-studentName').val(name);
                $('#edit-dob').val(dob);
                $('#edit-email').val(email);
                $('#edit-address').val(address);
                $('#edit-department').val(dept);

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
<?php $conn->close(); ?>
