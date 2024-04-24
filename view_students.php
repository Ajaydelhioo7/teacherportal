<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    // If the teacher is not logged in, redirect to login page
    header("Location: login_teacher.php");
    exit();
}

require_once './database/db.php'; // Adjust the path as needed

// Message initialization
$message = '';

// Deletion logic
if (isset($_GET['delete_id'])) {
    $student_id = $_GET['delete_id'];
    $delete_stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $delete_stmt->bind_param("i", $student_id);
    if ($delete_stmt->execute()) {
        $message = "Student with ID $student_id has been deleted.";
    } else {
        $message = "Error: " . $delete_stmt->error;
    }
    $delete_stmt->close();
    // Redirect to avoid re-execution on refresh
    $_SESSION['message'] = $message;
    header('Location: view_students.php');
    exit();
}

// Fetching students logic
$students = [];
$select_stmt = $conn->prepare("SELECT * FROM students");
$select_stmt->execute();
$result = $select_stmt->get_result();
if ($result) {
    $students = $result->fetch_all(MYSQLI_ASSOC);
}
$select_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="../css/style.css">
    <!-- ... Your head content like title, meta tags, css links ... -->
</head>
<body>
<?php include './includes/header.php'; ?>
<!-- ... Your navigation, header or sidebar ... -->

<?php
if (!empty($_SESSION['message'])) {
    echo "<p>{$_SESSION['message']}</p>";
    unset($_SESSION['message']); // Clear the message
}
?>

<h2>Student List</h2>
<table  class="table table-striped">
    <tr>
        <th>ID</th>
        <th>Roll No</th>
        <th>Name</th>
        <th>Batch</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($students as $student): ?>
    <tr>
        <td><?php echo htmlspecialchars($student['id']); ?></td>
        <td><?php echo htmlspecialchars($student['rollno']); ?></td>
        <td><?php echo htmlspecialchars($student['name']); ?></td>
        <td><?php echo htmlspecialchars($student['batch']); ?></td>
        <td><?php echo htmlspecialchars($student['email']); ?></td>
        <td><?php echo htmlspecialchars($student['phone']); ?></td>
        <td>
            <a class="btn btn-danger btn-block " href="view_students.php?delete_id=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include './includes/footer.php'; ?>
</body>
</html>
