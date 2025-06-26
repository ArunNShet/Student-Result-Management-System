<?php
$conn = new mysqli('localhost', 'root', '', 'student_test');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['usn'])) {
    $usn = $conn->real_escape_string($_POST['usn']);
    $sql = "SELECT sname FROM student_info WHERE usn = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo htmlspecialchars($row['sname']);
    } else {
        echo "No student found with this USN.";
    }

    $stmt->close();
}

$conn->close();
?>
