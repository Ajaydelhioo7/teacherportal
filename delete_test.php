<?php
session_start();

// Check for a message and clear it after displaying
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    echo "<p>$message</p>"; // Display the message
}


if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Database connection
$message = ''; // To store messages to display after redirects


// Handle Delete Test
if (isset($_GET['delete_test'])) {
    $test_id = $_GET['delete_test'];
    $stmt = $conn->prepare("DELETE FROM Tests WHERE id = ? AND createdby = ?");
    $stmt->bind_param("ii", $test_id, $_SESSION['teacher_id']);
    if ($stmt->execute()) {
        echo "<p>Test deleted successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();

     // Redirect to prevent form resubmission
     $_SESSION['message'] = 'Test deleted successfully!';
     header('Location: delete_test.php');
     exit();
}
// Fetch all tests created by the logged-in teacher
$stmt = $conn->prepare("SELECT * FROM Tests WHERE createdby = ?");
$stmt->bind_param("i", $_SESSION['teacher_id']);
$stmt->execute();
$tests_result = $stmt->get_result();


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
    <!-- <h4>Welcome, <?php echo $_SESSION['teacher_name']; ?></h4> -->


    <h4 class="mt-3">Your Tests</h4>
    <table border="1" class="table table-striped mt-3">
        <tr>
            <th>ID</th>
            <th>Test Name</th>
            <th>Batch</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($test = $tests_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($test['id']); ?></td>
                <td><?php echo htmlspecialchars($test['testname']); ?></td>
                <td><?php echo htmlspecialchars($test['batch']); ?></td>
                <td><?php echo htmlspecialchars($test['date']); ?></td>
                <td>
                    <!-- <a href="view_test_scores.php?test_id=<?php echo $test['id']; ?>">View Scores</a> | -->
                    <a class="btn btn-danger btn-block " href="delete_test.php?delete_test=<?php echo $test['id']; ?>" onclick="return confirm('Are you sure you want to delete this test?');">Delete Test</a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <?php include './includes/footer.php'; ?>
</body>
</html>


<?php
$conn->close();
?>