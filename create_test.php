<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Include your database connection file

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

$subjects = array();
$subjectQuery = "SELECT subject FROM subject_awards";
$subjectResult = $conn->query($subjectQuery);
if ($subjectResult) {
    while ($row = $subjectResult->fetch_assoc()) {
        $subjects[] = $row['subject'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_test'])) {
    $testname = $_POST['testname'];
    $batch = $_POST['batch'];
    $total_questions = (int)$_POST['total_questions'];
    $award_for_right = (float)$_POST['award_for_right'];
    $award_for_wrong = (float)$_POST['award_for_wrong'];
    $subject = $_POST['subject'];
    $created_by = $_SESSION['teacher_id'];

    // Calculate max_marks
    $max_marks = $total_questions * $award_for_right;

    $stmt = $conn->prepare("INSERT INTO tests (testname, batch, max_marks, total_questions, award_for_right, award_for_wrong, subject, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $message = "Error preparing statement: " . $conn->error;
    } else {
        $stmt->bind_param("ssiiddsi", $testname, $batch, $max_marks, $total_questions, $award_for_right, $award_for_wrong, $subject, $created_by);
        if ($stmt->execute()) {
            $message = "New test added successfully!";
        } else {
            $message = "Error adding test: " . $stmt->error;
        }
        $stmt->close();
    }

    $_SESSION['message'] = $message;
    header('Location: create_test.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include './includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title text-center mb-4">Create New Test</h4>
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo strpos($message, 'successfully') !== false ? 'success' : 'danger'; ?>">
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>
                        <form action="create_test.php" method="post">
                            <div class="form-group">
                                <label for="testname">Test Name:</label>
                                <input type="text" class="form-control" id="testname" name="testname" required>
                            </div>
                            <div class="form-group">
                                <label for="batch">Batch:</label>
                                <input type="text" class="form-control" id="batch" name="batch" required>
                            </div>
                            <div class="form-group">
                                <label for="total_questions">Total Questions:</label>
                                <input type="number" class="form-control" id="total_questions" name="total_questions" required>
                            </div>
                            <div class="form-group">
                                <label for="award_for_right">Award for Right Answer:</label>
                                <input type="text" class="form-control" id="award_for_right" name="award_for_right" required>
                            </div>
                            <div class="form-group">
                                <label for="award_for_wrong">Award for Wrong Answer:</label>
                                <input type="number" class="form-control" id="award_for_wrong" name="award_for_wrong" step="0.01" required>
                            </div>
                            <button type="submit" class="btn btn-warning text-dark btn-block" name="add_test">Add Test</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.9.3/umd.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
</body>
</html>
