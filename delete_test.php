<?php
session_start();

require './database/db.php'; // Use require to ensure the file must be loaded

// Check if the user is logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

// Check if the delete action has been requested
if (isset($_POST['delete_test'])) {
    // Add a CSRF token check here for security
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "CSRF token mismatch.";
        header('Location: delete_test.php');
        exit();
    }

    $test_id = $_POST['test_id'];
    $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $test_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Test deleted successfully!";
        } else {
            $_SESSION['message'] = "Error deleting test: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Error preparing statement: " . $conn->error;
    }
    header('Location: delete_test.php'); // Redirect to prevent form resubmission issues
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
                    <td><?= htmlspecialchars($test['id']); ?></td>
                    <td><?= htmlspecialchars($test['testname']); ?></td>
                    <td><?= htmlspecialchars($test['batch']); ?></td>
                    <td><?= htmlspecialchars($test['max_marks']); ?></td>
                    <td>
                        <form action="delete_test.php" method="post">
                            <input type="hidden" name="test_id" value="<?= $test['id']; ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                            <button type="submit" name="delete_test" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this test?');">Delete</button>
                        </form>
                    </td>
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
