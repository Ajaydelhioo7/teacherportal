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

include '../database/db.php'; // Database connection
$message = ''; // To store messages to display after redirects




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="../css/teacher_dashboard.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
<?php include './includes/header.php'; ?>
    <h4>Welcome, <?php echo $_SESSION['teacher_name']; ?></h4>

</body>
</html>
<?php include './includes/footer.php'; ?>

<?php
$conn->close();
?>
