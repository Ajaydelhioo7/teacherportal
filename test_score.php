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



// Handle Add Score
if (isset($_POST['add_score'])) {
    $rollno = $_POST['rollno'];
    $batch = $_POST['score_batch'];
    $testname = $_POST['score_testname'];
    $testid = $_POST['score_testid'];
    $totalmarks = $_POST['totalmarks'];
    $maximumscore = $_POST['maximumscore']; // Retrieve the maximum score from the form
    $rightquestion = $_POST['rightquestion'];
    $wrongquestion = $_POST['wrongquestion'];
    $notattempted = $_POST['notattempted'];

    // Calculate percentage dynamically
    $percentage = ($totalmarks / $maximumscore) * 100; // Updated calculation

    // Prepare SQL with the new column
    $stmt = $conn->prepare("INSERT INTO TestScores (rollno, batch, testname, testid, totalmarks, rightquestion, wrongquestion, notattempted, maximumscore, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisiiiidi", $rollno, $batch, $testname, $testid, $totalmarks, $rightquestion, $wrongquestion, $notattempted, $maximumscore, $percentage);

    if ($stmt->execute()) {
        // Set the success message
        $_SESSION['message'] = 'Score added successfully!';
    } else {
        // Set the error message
        $_SESSION['message'] = "Error adding score: " . $stmt->error;
    }
    $stmt->close();

    // Redirect to prevent form resubmission
    header('Location: test_score.php');
    exit();
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <!-- <link rel="stylesheet" href="../css/teacher_dashboard.css">
    <link rel="stylesheet" href="../css/style.css"> -->
</head>

<body>
<?php include './includes/header.php'; ?>
    <!-- <h4>Welcome, <?php echo $_SESSION['teacher_name']; ?></h4> -->
    <div class="container">
        <h4 class="text-center p-3">Add Scores for a Test</h4>
        <div class="bg-white p-5 rounded-3 shadow">
            <form action="test_score.php" method="post">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="rollno" class="form-label">Roll No:</label>
                        <input type="text" id="rollno" class="form-control" name="rollno" required>
                    </div>
                    <div class="col-md-4">
                        <label for="score_batch" class="form-label">Batch:</label>
                        <input type="text" id="score_batch" class="form-control" name="score_batch" required>
                    </div>
                    <div class="col-md-4">
                        <label for="score_testname" class="form-label">Test Name:</label>
                        <input type="text" id="score_testname" class="form-control" name="score_testname" required>
                    </div>
                    <div class="col-md-4">
                        <label for="score_testid" class="form-label">Test ID:</label>
                        <input type="text" id="score_testid" class="form-control" name="score_testid" required>
                    </div>
                    <div class="col-md-4">
                        <label for="totalmarks" class="form-label">Marks Obtained:</label>
                        <input type="number" id="totalmarks" class="form-control" name="totalmarks" required>
                    </div>
                    <div class="col-md-4">
                        <label for="maximumscore" class="form-label">Max Marks:</label>
                        <input type="number" id="maximumscore" class="form-control" name="maximumscore" required>
                    </div>
                    <div class="col-md-4">
                        <label for="rightquestion" class="form-label">Right Questions:</label>
                        <input type="number" id="rightquestion" class="form-control" name="rightquestion" required>
                    </div>
                    <div class="col-md-4">
                        <label for="wrongquestion" class="form-label">Wrong Questions:</label>
                        <input type="number" id="wrongquestion" class="form-control" name="wrongquestion" required>
                    </div>
                    <div class="col-md-4">
                        <label for="notattempted" class="form-label">Not Attempted:</label>
                        <input type="number" id="notattempted" class="form-control" name="notattempted" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_score" class="btn btn-warning btn-block mt-4">Add Score</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>

</body>
</html>


<?php
$conn->close();
?>