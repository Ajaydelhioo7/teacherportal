<?php
session_start();
require './database/db.php'; // Assuming db.php has the database connection properly set up

require __DIR__ . '/vendor/autoload.php'; // PhpSpreadsheet autoload
use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit;
}

// Fetch tests for the dropdown dynamically
$stmt = $conn->prepare("SELECT testname, max_marks, award_for_wrong, award_for_right FROM tests");
if (!$stmt) {
    die('MySQL prepare error: ' . $conn->error);
}
$stmt->execute();
$tests_result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['submit'])) {
        // Handling form submission for individual test score entries
        $rollno = $_POST['rollno'];
        $batch = $_POST['batch'];
        $testname = $_POST['testname'];
        $right_question = $_POST['right_question'];
        $wrong_question = $_POST['wrong_question'];
        $not_attempted = $_POST['not_attempted'];

        $stmt = $conn->prepare("SELECT max_marks, award_for_wrong, award_for_right FROM tests WHERE testname = ?");
        if (!$stmt) {
            echo 'MySQL prepare error: ' . $conn->error;
            exit;
        }
        $stmt->bind_param("s", $testname);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (!$data) {
            echo "Error retrieving test details.";
            exit;
        }

        $max_marks =(float) $data['max_marks'];
        $award_for_wrong = (float)$data['award_for_wrong'];
        $award_for_right = (float)$data['award_for_right'];

        $marks_obtained = ($right_question * $award_for_right) + ($wrong_question * $award_for_wrong);
        $percentage = ($marks_obtained / $max_marks) * 100.0;

        $insertStmt = $conn->prepare("INSERT INTO Test_Scores (rollno, batch, testname, right_question, wrong_question, not_attempted, max_marks, award_for_wrong, award_for_right, marks_obtained, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$insertStmt) {
            echo 'MySQL prepare error: ' . $conn->error;
            exit;
        }
        $insertStmt->bind_param("isssiiididd", $rollno, $batch, $testname, $right_question, $wrong_question, $not_attempted, $max_marks, $award_for_wrong, $award_for_right, $marks_obtained, $percentage);
        if (!$insertStmt->execute()) {
            echo "Error: " . $insertStmt->error;
        } else {
            $_SESSION['message'] = "Record added successfully";
            $_SESSION['message_type'] = 'success';
            header('Location: test_score.php');
            exit;
        }
    } elseif (isset($_POST['upload'])) {
        // Handling Excel file uploads for bulk entries
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo "Upload failed with error code " . $_FILES['file']['error'];
            exit;
        }
        $file = $_FILES['file']['tmp_name'];
        $reader = IOFactory::createReaderForFile($file);
        $spreadsheet = $reader->load($file);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        foreach ($rows as $row) {
            // Assuming Excel file columns match database fields directly
            $testname = $row[2]; // Test name from Excel
            $right_question = $row[3]; // Right questions from Excel
            $wrong_question = $row[4]; // Wrong questions from Excel
            $not_attempted = $row[5]; // Not attempted questions from Excel

            $stmt = $conn->prepare("SELECT max_marks, award_for_wrong, award_for_right FROM tests WHERE testname = ?");
            if (!$stmt) {
                echo 'MySQL prepare error: ' . $conn->error;
                continue; // Continue with next row in case of error
            }
            $stmt->bind_param("s", $testname);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            if (!$data) {
                echo "Error retrieving test details for test: $testname";
                continue; // Continue with next row if test details not found
            }

            $max_marks = $data['max_marks'];
            $award_for_wrong = $data['award_for_wrong'];
            $award_for_right = $data['award_for_right'];

            $marks_obtained = ($right_question * $award_for_right) + ($wrong_question * $award_for_wrong);
            $percentage = ($marks_obtained / $max_marks) * 100;

            $insertStmt = $conn->prepare("INSERT INTO Test_Scores (rollno, batch, testname, right_question, wrong_question, not_attempted, max_marks, award_for_wrong, award_for_right, marks_obtained, percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$insertStmt) {
                echo 'MySQL prepare error: ' . $conn->error;
                continue; // Continue with next row in case of error
            }
            $insertStmt->bind_param("isssiiididd", $row[0], $row[1], $testname, $right_question, $wrong_question, $not_attempted, $max_marks, $award_for_wrong, $award_for_right, $marks_obtained, $percentage);
            if (!$insertStmt->execute()) {
                echo "Failed to insert data: " . $insertStmt->error;
            }
        }
        $_SESSION['message'] = "Excel records added successfully";
        $_SESSION['message_type'] = 'success';
        header('Location: test_score.php');
        exit;
    }
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
    <?php include('./includes/header.php'); ?>
    <div class="container mt-5">
        <h4 class="mb-4">Add Test Score</h4>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?= $_SESSION['message_type'] == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <?= $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
        <form method="post" action="test_score.php" class="needs-validation" novalidate enctype="multipart/form-data">
            <div class="mb-3">
                <label for="rollno" class="form-label">Roll No:</label>
                <input type="number" class="form-control" name="rollno" id="rollno" required>
                <div class="invalid-feedback">Please provide a valid roll number.</div>
            </div>
            <div class="mb-3">
                <label for="batch" class="form-label">Batch:</label>
                <input type="text" class="form-control" name="batch" id="batch" required>
                <div class="invalid-feedback">Please provide a batch.</div>
            </div>
            <div class="mb-3">
                <label for="testname" class="form-label">Test Name:</label>
                <select class="form-select" name="testname" id="testname" required onchange="fetchAwardForRight(this.value);">
                    <option value="">Select Test</option>
                    <?php while ($test = $tests_result->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($test['testname']); ?>"><?= htmlspecialchars($test['testname']); ?></option>
                    <?php endwhile; ?>
                </select>
                <div class="invalid-feedback">Please select a test name.</div>
            </div>
            <div class="mb-3">
                <label for="right_question" class="form-label">Right Questions:</label>
                <input type="number" class="form-control" name="right_question" id="right_question" required>
                <div class="invalid-feedback">Please enter the number of right questions.</div>
            </div>
            <div class="mb-3">
                <label for="wrong_question" class="form-label">Wrong Questions:</label>
                <input type="number" class="form-control" name="wrong_question" id="wrong_question" required>
                <div class="invalid-feedback">Please enter the number of wrong questions.</div>
            </div>
            <div class="mb-3">
                <label for="not_attempted" class="form-label">Not Attempted:</label>
                <input type="number" class="form-control" name="not_attempted" id="not_attempted" required>
                <div class="invalid-feedback">Please enter the number of not attempted questions.</div>
            </div>
            <button type="submit" name="submit" class="btn btn-warning text-dark">Add Score</button>

            <!-- File Upload Field for Bulk Upload -->
            <div class="mb-3">
                <label for="file" class="form-label">Upload Excel File:</label>
                <input type="file" class="form-control" name="file" id="file" accept=".xls, .xlsx" required>
                <div class="invalid-feedback">Please upload an Excel file.</div>
            </div>
            <button type="submit" name="upload" class="btn btn-primary">Upload File</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function fetchAwardForRight(testName) {
        $.ajax({
            url: 'fetch_award_for_right.php',
            method: 'POST',
            data: {testname: testName},
            success: function(data) {
                var awards = JSON.parse(data);
                $('#award_for_right').val(awards['award_for_right']);
                $('#max_marks').val(awards['max_marks']);
                $('#award_for_wrong').val(awards['award_for_wrong']);
            }
        });
    }
</script>

    <?php include('./includes/footer.php'); ?>
</body>
</html>
