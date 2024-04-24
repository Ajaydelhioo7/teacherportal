<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login</title>
    <link rel="stylesheet" href="./assets/css/login.css">
</head>
<body>

    <div class="container ">
    <h1>Teacher Login</h1>
    <form action="login_teacher.php" method="post">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <input type="submit" name="submit" value="Login">
    </form>
    </div>
    <?php
session_start(); // Start the session at the very beginning

include './database/db.php'; // Database connection

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Teachers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Login success
        $_SESSION['teacher_id'] = $user['id'];
        $_SESSION['teacher_name'] = $user['username'];
        header("Location: teacher_dashboard.php"); // Redirect to the teacher dashboard
        exit();
    } else {
        // Login failed
        echo "Invalid Username or Password!";
    }

    $stmt->close();
    $conn->close();
}
?>
</body>
</html>
