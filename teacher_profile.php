<?php
session_start();

// Check if the teacher is logged in, otherwise redirect to login page
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Adjust the path to your database connection file as needed

$message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get POST data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    // Update password only if it's provided
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Update query
    $update_sql = "UPDATE Teachers SET username=?, email=?, phone=? ".($password ? ", password=?" : "")." WHERE id=?";
    $stmt = $conn->prepare($update_sql);

    if ($password) {
        $stmt->bind_param("sssii", $username, $email, $phone, $password, $_SESSION['teacher_id']);
    } else {
        $stmt->bind_param("ssii", $username, $email, $phone, $_SESSION['teacher_id']);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
    } else {
        $message = "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch current teacher data
$teacher_data = [];
if (isset($_SESSION['teacher_id'])) {
    $stmt = $conn->prepare("SELECT username, email, phone FROM Teachers WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['teacher_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher_data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <!-- Link to your CSS file -->
</head>
<body>
    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>
    <?php include('./includes/header.php')?>
    <!-- <form method="post" action="teacher_profile.php">
        Username: <input type="text" name="username" value="<?php echo htmlspecialchars($teacher_data['username'] ?? ''); ?>" required><br>
        Email: <input type="email" name="email" value="<?php echo htmlspecialchars($teacher_data['email'] ?? ''); ?>" required><br>
        Phone: <input type="text" name="phone" value="<?php echo htmlspecialchars($teacher_data['phone'] ?? ''); ?>" required><br>
        Password: <input type="password" name="password"><br>
        <small>Leave the password field blank if you do not want to change it.</small><br>
        <input type="submit" value="Update Profile">
    </form> -->
    

    <main role="main" class="container">
        <h4 class="mt-5">Edit Profile</h4>
        <?php if ($message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="teacher_profile.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($teacher_data['username'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($teacher_data['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($teacher_data['phone'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password">
                <small class="form-text text-muted">Leave the password field blank if you do not want to change it.</small>
            </div>
            <button type="submit" class="btn btn-warning text-dark">Update Profile</button>
        </form>
    </main>
<?php include('./includes/footer.php')?>
</body>
</html>
