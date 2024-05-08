<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Include your database connection file

// To store messages for the user
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
} else {
    $message = '';
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get data from the form
    $batch = $_POST['batch'];
    $testname = $_POST['testname'];
    $maxmarks = $_POST['maxmarks'];

    // Prepare an SQL statement to avoid SQL injection
    $stmt = $conn->prepare("INSERT INTO create_mains_test (batch, testname, maxmarks) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $batch, $testname, $maxmarks);

    // Execute the statement and check if it was successful
    if ($stmt->execute()) {
        $_SESSION['message'] = "Test created successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect to the same page to prevent form resubmission
    header("Location: create_mains_test.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Main Test</title>
    <!-- Bootstrap CSS already included -->
    <link href="path/to/your/bootstrap.css" rel="stylesheet">
</head>
<body>
<?php include './includes/header.php'; ?>
<div class="container">
    <h4>Create Main Test</h4>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form action="create_mains_test.php" method="post">
        <div class="form-group">
            <label for="batch">Batch:</label>
            <input type="text" class="form-control" id="batch" name="batch" required>
        </div>
        <div class="form-group">
            <label for="testname">Test Name:</label>
            <input type="text" class="form-control" id="testname" name="testname" required>
        </div>
        <div class="form-group">
            <label for="maxmarks">Maximum Marks:</label>
            <input type="number" class="form-control" id="maxmarks" name="maxmarks" required>
        </div>
        <button type="submit" class="btn btn-warning">Submit</button>
    </form>
</div>

<?php include './includes/footer.php'; ?>
</body>
</html>
