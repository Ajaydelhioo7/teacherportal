<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

require_once './database/db.php'; // Adjust the path as needed

$message = '';

// Handle Delete
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
    $_SESSION['message'] = $message;
    header('Location: view_students.php');
    exit();
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $rollno = $_POST['rollno'];
    $name = $_POST['name'];
    $batch = $_POST['batch'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $update_stmt = $conn->prepare("UPDATE students SET rollno = ?, name = ?, batch = ?, email = ?, phone = ? WHERE id = ?");
    $update_stmt->bind_param("sssssi", $rollno, $name, $batch, $email, $phone, $id);
    if ($update_stmt->execute()) {
        $message = "Student updated successfully.";
    } else {
        $message = "Update failed: " . $update_stmt->error;
    }
    $update_stmt->close();
    $_SESSION['message'] = $message;
    header('Location: view_students.php');
    exit();
}

// Fetch Students
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>

<div class="container mt-5">
    <?php
    if (!empty($_SESSION['message'])) {
        echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
    }
    ?>

    <h2>Student List</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Roll No</th>
                <th>Name</th>
                <th>Batch</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $student): ?>
            <tr>
                <form method="POST" action="view_students.php">
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><input type="text" name="rollno" value="<?php echo htmlspecialchars($student['rollno']); ?>" class="form-control"></td>
                    <td><input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" class="form-control"></td>
                    <td><input type="text" name="batch" value="<?php echo htmlspecialchars($student['batch']); ?>" class="form-control"></td>
                    <td><input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" class="form-control"></td>
                    <td><input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" class="form-control"></td>
                    <td>
                        <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                        <button type="submit" name="update" class="btn btn-success">Update</button>
                        <a href="view_students.php?delete_id=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
                    
<?php include './includes/footer.php'; ?>
</body>
</html>
