<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['semester'])) {
    $semester_id = intval($_POST['semester']);

    // Fetch subjects for the semester
    $subject_sql = "SELECT subject.id AS subject_code, subject.subject_name 
                    FROM subject 
                    INNER JOIN subjectcomb 
                    ON subject.id = subjectcomb.subid 
                    WHERE subjectcomb.courseid = ?";
    $stmt = $conn->prepare($subject_sql);
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    // Return data as JSON
    echo json_encode(['subjects' => $subjects]);
    $stmt->close();
}

$conn->close();
?>
