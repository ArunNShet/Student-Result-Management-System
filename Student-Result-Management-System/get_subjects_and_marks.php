<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'student_test');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['usn'], $_GET['sem_id'])) {
    $usn = $conn->real_escape_string($_GET['usn']);
    $sem_id = intval($_GET['sem_id']);
    
    // Fetch subjects and marks for the selected student and semester
    $query = "SELECT s.subject_name, r.marks, s.id AS subject_code
              FROM result r 
              JOIN subject s ON r.subject_code = s.id 
              WHERE r.usn = ? AND r.sem_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $usn, $sem_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="form-group">';
            echo '<label>' . htmlspecialchars($row['subject_name']) . '</label>';
            echo '<input type="number" class="form-control" name="marks[]" value="' . htmlspecialchars($row['marks']) . '" required>';
            echo '<input type="hidden" name="subject_code[]" value="' . htmlspecialchars($row['subject_code']) . '">';
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">No subjects found for this semester.</div>';
    }
}

$conn->close();
?>
