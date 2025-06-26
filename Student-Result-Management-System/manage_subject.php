<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all subjects
$subjects = [];
$query = "SELECT * FROM subject";
$result = $conn->query($query);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Handle update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_subject'])) {
    $id = $_POST['id'];
    $subjectName = $_POST['subject_name'];
    $subjectCode = $_POST['subject_code'];

    $update_query = "UPDATE subject SET subject_name = ?, subject_code = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $subjectName, $subjectCode, $id);

    if ($stmt->execute()) {
        header("Location: manage_subject.php?success=1");
        exit();
    } else {
        $errorMessage = "Error updating subject: " . $stmt->error;
    }
}

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
        <!-- Success message -->
        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">Subject updated successfully!</div>
        <?php endif; ?>
        <h2 class="sub-heading">Manage Subjects</h2>
        <!-- Subject table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject Name</th>
                    <th>Subject Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($subjects)) : ?>
                    <?php foreach ($subjects as $subject) : ?>
                        <tr>
                            <td><?= htmlspecialchars($subject['id']); ?></td>
                            <td><?= htmlspecialchars($subject['subject_name']); ?></td>
                            <td><?= htmlspecialchars($subject['subject_code']); ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-button"
                                        data-id="<?= $subject['id']; ?>"
                                        data-name="<?= htmlspecialchars($subject['subject_name']); ?>"
                                        data-code="<?= htmlspecialchars($subject['subject_code']); ?>">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No subjects found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Edit modal -->
        <div id="editModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Edit Subject</h4>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-id" name="id">
                            <div class="form-group">
                                <label for="edit-subject-name">Subject Name:</label>
                                <input type="text" id="edit-subject-name" name="subject_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-subject-code">Subject Code:</label>
                                <input type="text" id="edit-subject-code" name="subject_code" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="edit_subject" class="btn btn-primary">Save Changes</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for modal handling -->
    <script>
        $(document).ready(function () {
            $('.edit-button').on('click', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const code = $(this).data('code');

                $('#edit-id').val(id);
                $('#edit-subject-name').val(name);
                $('#edit-subject-code').val(code);

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
