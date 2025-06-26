<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if USN and semester ID are set in session
if (isset($_SESSION['usn']) && isset($_SESSION['semester'])) {
    $usn = $_SESSION['usn'];
    $semid = $_SESSION['semester']; // Use 'semester' here instead of 'semid'

    // Fetch student information
    $query = "
        SELECT 
            student_info.sname, 
            student_info.usn, 
            semester.dept_id, 
            semester.sem_name,
            department.dname 
        FROM 
            student_info
        JOIN 
            studentsem ON student_info.usn = studentsem.usn
        JOIN 
            semester ON semester.id = studentsem.semid
        JOIN
            department ON department.id = semester.dept_id
        WHERE 
            student_info.usn = ? 
            AND studentsem.semid = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $usn, $semid);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
    } else {
        $student_data = null;
    }

    // Fetch subject marks
    $query_results = "
        SELECT 
            r.subject_code, 
            s.subject_name, 
            r.marks
        FROM 
            result r
        JOIN 
            subject s ON r.subject_code = s.id
        WHERE 
            r.usn = ? 
            AND r.sem_id = ?";

    $stmt_results = $conn->prepare($query_results);
    $stmt_results->bind_param("si", $usn, $semid);
    $stmt_results->execute();
    $result_data = $stmt_results->get_result();
} else {
    $student_data = null;
    $result_data = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .college-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            text-align: center;
        }

        .college-header img {
            width: 80px;
            /* Adjust size as needed */
            height: auto;
        }

        .college-header .text-section {
            flex: 1;
            /* Allows text section to grow and center-align */
            margin: 0 20px;
            /* Adds spacing between images and text */
        }


        .college-header h1 {
            font-size: 20px;
            margin: 0;
            color: rgb(38, 76, 182);
            margin-bottom: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .college-header h3 {
            font-size: 16px;
            color: rgb(38, 76, 182);
            margin: 5px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .college-header h4 {
            font-size: 16px;
            color: rgb(16, 26, 133);
            margin: 5px;
            font-weight: bold;
        }

        .table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }

        .table th {
            background-color: #f7f7f7;
            font-weight: bold;
            text-transform: uppercase;
            color: #333;
        }

        .table .marks-head th {
            background-color: #007bff;
            color: #fff;
        }

        .table tbody tr:nth-child(odd) {
            background-color: #fafafa;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .print-button {
            text-align: center;
            margin-top: 20px;
        }

        .print-button .btn-primary {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-transform: uppercase;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .print-button .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .text-center {
            text-align: center;
            margin-top: 20px;
        }

        .text-center .btn-secondary {
            background-color: #6c757d;
            color: #fff;
            padding: 10px 13px;
            border: none;
            border-radius: 5px;
            text-transform: uppercase;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .text-center .btn-secondary:hover {
            background-color: rgb(104, 123, 138);
        }

        @media print {
            .text-center {
                display: none;
                /* Hide the Back to Home button */
            }

            .print-button {
                display: none;
                /* Hide the Print button */
            }
        }

        @media print {
            @page {
                margin: 0;
                /* Remove default browser margins */
            }

            body {
                margin: 1cm;
                /* Add custom margins to the content */
            }

            /* Hide unnecessary browser UI */
            header,
            footer {
                display: none !important;
            }

            .college-header h1 {
                font-size: 20px;
            }
        }
        @media (max-width:800px) {
            .college-header h1 {
                font-size: 17px;
            }
        }
        @media (max-width:428px) {
            .college-header img {
                width: 35px;
            }

            .college-header h4 {
                font-size: 9px;
                margin: 2px;
            }

            .college-header h1 {
                font-size: 11px;
            }

            .college-header .text-section {
                flex: 1;
                margin: 0;
            }

            .college-header h3 {
                font-size: 10px;
                margin: 0;
            }

            .table th,
            .table td {
                font-size: 7px;
                padding: 5px;
            }

            .print-button .btn-primary {
                padding: 5px 10px;
                font-size: 10px;
            }

            .text-center .btn-secondary {
                padding: 5px 5px;
                font-size: 10px;
            }
        }
        @media (max-width:325px) {
            .college-header img {
                width: 35px;
            }

            .college-header h4 {
                font-size: 7px;
                margin: 2px;
            }

            .college-header h1 {
                font-size: 8px;
            }

            .college-header .text-section {
                flex: 1;
                margin: 0;
            }

            .college-header h3 {
                font-size: 8px;
                margin: 0;
            }

            .table th,
            .table td {
                font-size: 5px;
            }

            .print-button .btn-primary {
                padding: 5px 10px;
                font-size: 10px;
            }

            .text-center .btn-secondary {
                padding: 5px 5px;
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="college-header">
            <img src="l-logo.png" alt="College Logo" class="left-logo">
            <div class="text-section">
                <h4>Enter soceity name is available</h4>
                <h1>Enter college name</h1>
                <h3>(Approved by university name)</h3>
            </div>
            <img src="r-logo.jpg" alt="College Logo" class="right-logo">
        </div>

        <?php if ($student_data && $result_data): ?>
            <table class="table table-bordered">
                <tr>
                    <th>Student Name:</th>
                    <td><b><?php echo htmlspecialchars($student_data['sname']); ?></b></td>
                </tr>
                <tr>
                    <th>USN:</th>
                    <td><?php echo htmlspecialchars($student_data['usn']); ?></td>
                </tr>
                <tr>
                    <th>Course:</th>
                    <td><?php echo htmlspecialchars($student_data['dname']); ?></td>
                </tr>
                <tr>
                    <th>Semester:</th>
                    <td><?php echo htmlspecialchars($student_data['sem_name']); ?></td>
                </tr>
            </table>

            <table class="table table-bordered">
                <div class="marks-head">
                    <thead>
                        <tr>
                            <th>SI NO</th>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                </div>
                <tbody>
                    <?php
                    $total_marks = 0;
                    $total_subjects = 0;
                    $serial_no = 1;  // Initialize serial number
                    $final_result = 'Pass'; // Assume the student passes by default

                    if ($result_data->num_rows > 0) {
                        while ($row = $result_data->fetch_assoc()) {

                            // Calculate total marks and subject count
                            $marks = $row['marks'];
                            $total_marks += $marks;
                            $total_subjects++;

                            // Check if the student failed in this subject
                            $subject_result = ($marks < 35) ? 'Fail' : 'Pass';

                            // If any subject is a fail, the final result is Fail
                            if ($subject_result == 'Fail') {
                                $final_result = 'Fail';
                            }

                            // Add serial number, subject name, marks, and result to the table
                            echo "<tr>
                    <td>" . $serial_no++ . "</td>
                    <td>" . htmlspecialchars($row['subject_name']) . "</td>
                    <td>" . htmlspecialchars($marks) . "</td>
                    <td style='color: " . ($subject_result == 'Pass' ? 'green' : 'red') . "; font-weight: bold;'>" . $subject_result . "</td>
                </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No records found for subjects.</td></tr>";
                    }
                    ?>
                </tbody>

            </table>

            <?php
            $max_marks = $total_subjects * 100;
            $percentage = ($total_subjects > 0) ? ($total_marks / $max_marks) * 100 : 0;
            ?>

            <table class="table table-bordered">
                <tr>
                    <th>Total Marks:</th>
                    <td><?php echo $total_marks . " / " . $max_marks; ?></td>
                </tr>
                <tr>
                    <th>Percentage:</th>
                    <td><?php echo number_format($percentage, 2); ?>%</td>
                </tr>
                <tr>
                    <th>Result:</th>
                    <td style="color: <?php echo ($final_result == 'Pass') ? 'green' : 'red'; ?>; font-weight: bold;">
                        <?php echo $final_result; ?>
                    </td>
                </tr>
            </table>


            <div class="print-button text-center">
                <button class="btn btn-primary" onclick="window.print();">Print Result</button>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <?php echo 'Student not found with this USN or invalid semester.'; ?>
            </div>
        <?php endif; ?>

        <div class="text-center">
            <a href="login.php" class="btn btn-secondary">Back to Home</a>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>