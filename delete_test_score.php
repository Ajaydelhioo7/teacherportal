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


// Handle Delete Score
if (isset($_GET['delete_score'])) {
    $rollno = $_GET['rollno'];
    $testid = $_GET['testid'];

    $stmt = $conn->prepare("DELETE FROM TestScores WHERE rollno = ? AND testid = ?");
    $stmt->bind_param("ii", $rollno, $testid);
    if ($stmt->execute()) {
        echo "<p>Score deleted successfully!</p>";
    } else {
        echo "<p>Error deleting score: " . $stmt->error . "</p>";
    }
    $stmt->close();
    // Redirect to prevent form resubmission
    $_SESSION['message'] = 'Score deleted successfully!';
    header('Location: delete_test_score.php');
    exit();
}


// Fetch Scores by Roll Number
$scores = [];
if (isset($_POST['view_scores'])) {
    $rollno = $_POST['rollno_view'];
    $stmt = $conn->prepare("SELECT * FROM TestScores WHERE rollno = ?");
    $stmt->bind_param("i", $rollno);
    $stmt->execute();
    $result = $stmt->get_result();
    $scores = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}



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
    <div class="container mt-5">
    <h4 class="text-center mb-4">View Scores by Roll Number</h4>
    <form action="delete_test_score.php" method="post" class="mb-4">
        <div class="form-row align-items-center">
            <div class="col-auto">
                <label class="sr-only" for="rollno_view">Roll No:</label>
                <input type="text" class="form-control mb-2" id="rollno_view" name="rollno_view" placeholder="Enter roll number" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-warning mb-2" name="view_scores">View Scores</button>
            </div>
        </div>
    </form>

    <h4 class="mb-3">Scores for Roll No: <?php echo htmlspecialchars($rollno); ?></h4>
    <table class="table table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Test ID</th>
                <th>Batch</th>
                <th>Total Marks</th>
                <th>Right Questions</th>
                <th>Wrong Questions</th>
                <th>Not Attempted</th>
                <th>Percentage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scores as $score): ?>
            <tr>
                <td><?php echo htmlspecialchars($score['testid']); ?></td>
                <td><?php echo htmlspecialchars($score['batch']); ?></td>
                <td><?php echo htmlspecialchars($score['totalmarks']); ?></td>
                <td><?php echo htmlspecialchars($score['rightquestion']); ?></td>
                <td><?php echo htmlspecialchars($score['wrongquestion']); ?></td>
                <td><?php echo htmlspecialchars($score['notattempted']); ?></td>
                <td><?php echo htmlspecialchars($score['percentage']); ?>%</td>
                <td>
                    <a href="delete_test_score.php?delete_score=1&rollno=<?php echo $score['rollno']; ?>&testid=<?php echo $score['testid']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this score?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include './includes/footer.php'; ?>

</body>
</html>


<?php
$conn->close();
?>