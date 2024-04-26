<?php
session_start();
include './database/db.php';  // Assuming db.php has the database connection

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch tests for the dropdown and max_marks dynamically
$stmt = $conn->prepare("SELECT testname, max_marks, award_for_wrong, award_for_right FROM tests");
$stmt->execute();
$tests_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    $rollno = $_POST['rollno'];
    $batch = $_POST['batch'];
    $testname = $_POST['testname'];
    $right_question = $_POST['right_question'];
    $wrong_question = $_POST['wrong_question'];
    $not_attempted = $_POST['not_attempted'];

    // Retrieve max_marks, award_for_wrong, and award_for_right from the database based on testname for record accuracy
    $stmt = $conn->prepare("SELECT max_marks, award_for_wrong, award_for_right FROM tests WHERE testname = ?");
    $stmt->bind_param("s", $testname);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $max_marks = $data['max_marks'];
    $award_for_wrong = $data['award_for_wrong'];
    $award_for_right = $data['award_for_right'];

    // Calculate marks obtained
    $marks_obtained = ($right_question * $award_for_right) + ($wrong_question * $award_for_wrong);
    // Calculate percentage
    $percentage = ($marks_obtained / $max_marks) * 100;

    // Insert into Test_Scores table
    $insertStmt = $conn->prepare("INSERT INTO Test_Scores (rollno, batch, testname, right_question, wrong_question, not_attempted, max_marks, award_for_wrong, award_for_right, marks_obtained, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("isssiiididd", $rollno, $batch, $testname, $right_question, $wrong_question, $not_attempted, $max_marks, $award_for_wrong, $award_for_right, $marks_obtained, $percentage);
    if ($insertStmt->execute()) {
        $_SESSION['message'] = "Record added successfully"; // Store success message in session
        $_SESSION['message_type'] = 'success';
        $insertStmt->close();
        header('Location: test_score.php'); // Redirect to the same page
        exit();
    } else {
        echo "Error: " . $insertStmt->error; // Handle error without redirect
    }
    $insertStmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Test Scores</title>
</head>
<body>
    <?php include('./includes/header.php')?>
    <div class="container mt-5">
        <h4 class="mb-4">Add Test Score</h4>
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="alert ' . ($_SESSION['message_type'] == 'success' ? 'alert-success' : 'alert-danger') . '">' . $_SESSION['message'] . '</div>';
            unset($_SESSION['message'], $_SESSION['message_type']);
        }
        ?>
        <form method="post" action="test_score.php" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="rollno" class="form-label">Roll No:</label>
                <input type="number" class="form-control" name="rollno" id="rollno" required>
                <div class="invalid-feedback">
                    Please provide a valid roll number.
                </div>
            </div>
            <div class="mb-3">
                <label for="batch" class="form-label">Batch:</label>
                <input type="text" class="form-control" name="batch" id="batch" required>
                <div class="invalid-feedback">
                    Please provide a batch.
                </div>
            </div>
            <div class="mb-3">
                <label for="testname" class="form-label">Test Name:</label>
                <select class="form-select" name="testname" id="testname" required onchange="fetchAwardForRight(this.value);">
                    <option value="">Select Test</option>
                    <?php while ($test = $tests_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($test['testname']); ?>"><?= htmlspecialchars($test['testname']); ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="invalid-feedback">
                    Please select a test name.
                </div>
            </div>
            <div class="mb-3">
                <label for="right_question" class="form-label">Right Questions:</label>
                <input type="number" class="form-control" name="right_question" id="right_question" required>
                <div class="invalid-feedback">
                    Please enter the number of right questions.
                </div>
            </div>
            <div class="mb-3">
                <label for="wrong_question" class="form-label">Wrong Questions:</label>
                <input type="number" class="form-control" name="wrong_question" id="wrong_question" required>
                <div class="invalid-feedback">
                    Please enter the number of wrong questions.
                </div>
            </div>
            <div class="mb-3">
                <label for="not_attempted" class="form-label">Not Attempted:</label>
                <input type="number" class="form-control" name="not_attempted" id="not_attempted" required>
                <div class="invalid-feedback">
                    Please enter the number of not attempted questions.
                </div>
            </div>
            <input type="hidden" id="award_for_wrong" name="award_for_wrong"> <!-- Hidden field to store award_for_wrong value -->
            <button type="submit" name="submit" class="btn btn-warning">Add Score</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchAwardForRight(testName) {
            $.ajax({
                url: 'fetch_award_for_right.php', // This PHP file needs to return award_for_right for the given test name
                method: 'POST',
                data: {testname: testName},
                success: function(data) {
                    document.getElementById('award_for_right').value = data;
                }
            });
        }
    </script>
     <?php include('./includes/footer.php')?>
</body>
</html>
