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

// Handle Add Test
if (isset($_POST['add_test'])) {
    $testname = $_POST['testname'];
    $batch = $_POST['batch'];
    $date = $_POST['date'];
    $teacher_id = $_SESSION['teacher_id'];

    if (!empty($testname) && !empty($batch) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO Tests (testname, batch, createdby, date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $testname, $batch, $teacher_id, $date);
        if ($stmt->execute()) {
            echo "<p>New test added successfully!</p>";
        } else {
            echo "<p>Error adding test: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Please fill in all fields.</p>";
    }
    // Redirect to prevent form resubmission
    $_SESSION['message'] = 'New test added successfully!'; // Set the success message
    header('Location: create_test.php');
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
    <link rel="stylesheet" href="./assets/css/create_test.css"> -->
</head>

<body>
<?php include './includes/header.php'; ?>
    <!-- <h4>Welcome, <?php echo $_SESSION['teacher_name']; ?></h4> -->


   
    <!-- <div class="container">
        <div class="row">
        <div class="col-md-9 col-sm-12 m-auto formdiv mt-4 ">
        <h4 class="text-center">Create New Test</h4>
        <form action="create_test.php" method="post" class="p-4">
        Test Name: <input type="text" name="testname" required><br>
        Batch: <input type="text" name="batch" required><br>
        Date: <input type="date" name="date" required><br>
        <input class="bg-warning p-2"type="submit" name="add_test" value="Add Test">
    </form>

              <div> 
        <div>
  
    </div> -->
    <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="card-title text-center mb-4">Create New Test</h4>
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
                            <label for="date">Date:</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <button type="submit" name="add_test" class="btn btn-warning btn-block">Add Test</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


</body>
</html>
<?php include './includes/footer.php'; ?>

<?php
$conn->close();
?>