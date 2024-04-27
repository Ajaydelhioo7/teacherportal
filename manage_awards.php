<?php
session_start();

// Redirect to login page if the session is not set
if (!isset($_SESSION['teacher_id'])) {
    header('Location: teacher_login.php');
    exit();
}

// Include database connection file
require_once './database/db.php';

// Check for a session message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
} else {
    $message = '';
}

// If the form for adding a new award is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $subject = $conn->real_escape_string($_POST['subject']);
    $award_for_wrong = $conn->real_escape_string($_POST['award_for_wrong']);

    $sql = "INSERT INTO subject_awards (subject, award_for_wrong) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $subject, $award_for_wrong);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'New record created successfully.';
    } else {
        $_SESSION['message'] = 'Error: ' . $conn->error;
    }
    header('Location: manage_awards.php');
    exit();
}

// If the form for deleting an award is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $conn->real_escape_string($_POST['id']);

    $sql = "DELETE FROM subject_awards WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Record deleted successfully.';
    } else {
        $_SESSION['message'] = 'Error: ' . $conn->error;
    }
    header('Location: manage_awards.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Awards</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('./includes/header.php'); ?>
    <div class="container">
        <h4 class="mt-5">Manage Subject Awards</h4>
        <p><?php echo $message; ?></p>

        <!-- Add Award Form -->
        <div class="card mt-4">
            <div class="card-header">Add Subject Award</div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" class="form-control" name="subject" id="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="award_for_wrong">Award for Wrong</label>
                        <input type="text" class="form-control" name="award_for_wrong" id="award_for_wrong" required>
                    </div>
                    <button type="submit" name="add" class="btn btn-warning text-dark">Add Award</button>
                </form>
            </div>
        </div>

        <!-- Awards Table -->
        <div class="mt-5">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Subject</th>
                        <th scope="col">Award for Wrong</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM subject_awards";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<th scope='row'>". htmlspecialchars($row['id']) ."</th>";
                            echo "<td>". htmlspecialchars($row['subject']) ."</td>";
                            echo "<td>". htmlspecialchars($row['award_for_wrong']) ."</td>";
                            echo "<td>
                                    <form method='POST'>
                                        <input type='hidden' name='id' value='". htmlspecialchars($row['id']) ."'>
                                        <button type='submit' name='delete' class='btn btn-danger'>Delete</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include('./includes/footer.php'); ?>
    <!-- Include Bootstrap JS and its dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
</body>
</html>
