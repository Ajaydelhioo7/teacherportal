

                        <?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Include your database connection file

// Check if a notification message exists
$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
unset($_SESSION['message']); // Clear the session variable after displaying the message

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_test'])) {
    $testname = $_POST['testname'];
    $batch = $_POST['batch'];
    $max_marks = $_POST['max_marks'];
    $award_for_right = $_POST['award_for_right'];
    $subject = $_POST['subject'];
    $created_by = $_SESSION['teacher_id'];

    // Fetch the award_for_wrong value based on the selected subject
    $stmt = $conn->prepare("SELECT award_for_wrong FROM subject_awards WHERE subject = ?");
    $stmt->bind_param("s", $subject);
    $stmt->execute();
    $stmt->bind_result($award_for_wrong);
    $stmt->fetch();
    $stmt->close();

    // Prepare and bind the INSERT statement
    $stmt = $conn->prepare("INSERT INTO tests (testname, batch, max_marks, award_for_right, award_for_wrong, subject, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        $message = "Error preparing statement: " . $conn->error;
    } else {
        $stmt->bind_param("ssiddsi", $testname, $batch, $max_marks, $award_for_right, $award_for_wrong, $subject, $created_by); // Change datatype of award_for_wrong to 'd'

        // Execute the statement
        if ($stmt->execute()) {
            $message = "New test added successfully!";
        } else {
            $message = "Error adding test: " . $stmt->error;
        }
        $stmt->close();
    }

    $_SESSION['message'] = $message; // Store the message in session variable
    header('Location: create_test.php'); // Redirect to avoid form resubmission
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
                                <label for="max_marks">Max Marks:</label>
                                <input type="number" class="form-control" id="max_marks" name="max_marks" required>
                            </div>
                            <div class="form-group">
                                <label for="award_for_right">Award for Right Answer:</label>
                                <input type="number" class="form-control" id="award_for_right" name="award_for_right" required>
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <select class="form-control" id="subject" name="subject" required>
                                    <option value="csat">CSAT</option>
                                    <option value="gs">GS</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" name="add_test">Add Test</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/footer.php'; ?>
</body>
</html>
                      