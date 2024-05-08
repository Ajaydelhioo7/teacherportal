<?php
session_start();

require './database/db.php'; // Use require to ensure the file must be loaded

// Check if the user is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

// Handle Update or Delete action
if (isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "CSRF token mismatch.";
        header('Location: delete_test.php');
        exit();
    }

    $test_id = $_POST['test_id'];
    if ($_POST['action'] == 'delete') {
        $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Test deleted successfully!";
        } else {
            $_SESSION['message'] = "Error deleting test: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($_POST['action'] == 'update') {
        $testname = $_POST['testname'];
        $batch = $_POST['batch'];
        $max_marks = $_POST['max_marks'];
        $stmt = $conn->prepare("UPDATE tests SET testname = ?, batch = ?, max_marks = ? WHERE id = ?");
        $stmt->bind_param("ssii", $testname, $batch, $max_marks, $test_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Test updated successfully!";
        } else {
            $_SESSION['message'] = "Error updating test: " . $stmt->error;
        }
        $stmt->close();
    }
    header('Location: delete_test.php');
    exit();
}

// Fetch all tests to display
$stmt = $conn->prepare("SELECT * FROM tests");
$stmt->execute();
$tests_result = $stmt->get_result();

// Generate a new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Test</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>
<?php if (isset($_SESSION['message'])): ?>
    <p><?= $_SESSION['message']; ?></p>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<div class="container mt-5">
    <h4>Your Tests</h4>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Test Name</th>
                <th>Batch</th>
                <th>Max Marks</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($test = $tests_result->fetch_assoc()): ?>
            <tr>
                <form action="delete_test.php" method="post">
                    <td><?= htmlspecialchars($test['id']); ?></td>
                    <td><input type="text" name="testname" value="<?= htmlspecialchars($test['testname']); ?>" class="form-control"></td>
                    <td><input type="text" name="batch" value="<?= htmlspecialchars($test['batch']); ?>" class="form-control"></td>
                    <td><input type="number" name="max_marks" value="<?= htmlspecialchars($test['max_marks']); ?>" class="form-control"></td>
                    <td>
                        <input type="hidden" name="test_id" value="<?= $test['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="action" value="update" class="btn btn-primary">Update</button>
                        <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this test?');">Delete</button>
                    </td>
                </form>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include './includes/footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
