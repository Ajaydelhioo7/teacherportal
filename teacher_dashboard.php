<?php
session_start();

if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Database connection

// Fetch counts from the database
$teacherCount = $conn->query("SELECT COUNT(*) AS total FROM Teachers")->fetch_assoc()['total'];
$studentCount = $conn->query("SELECT COUNT(*) AS total FROM Students")->fetch_assoc()['total'];
$testCount = $conn->query("SELECT COUNT(*) AS total FROM Tests")->fetch_assoc()['total'];

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    echo "<p>$message</p>"; // Display the message
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/teacher_dashboard.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include './includes/header.php'; ?>

<div class="container mt-5">
    <h4 class="mb-4">Welcome, <?php echo $_SESSION['teacher_name']; ?></h4>
    <div class="row text-center">
        <div class="col-md-4">
            <a href="teacher_profile.php" class="card text-white bg-warning mb-3" style="max-width: 18rem;">
                <div class="card-header text-dark">Teachers</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $teacherCount; ?></h5>
                    <p class="card-text">Total Enrolled Teachers</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="view_students.php" class="card text-white bg-success mb-3" style="max-width: 18rem;">
                <div class="card-header text-dark">Students</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $studentCount; ?></h5>
                    <p class="card-text">Total Enrolled Students</p>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="delete_test.php" class="card text-white bg-danger mb-3" style="max-width: 18rem;">
                <div class="card-header text-dark">Tests</div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $testCount; ?></h5>
                    <p class="card-text">Total Tests Created</p>
                </div>
            </a>
        </div>
    </div>
</div>

<?php include './includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.9.3/umd.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
