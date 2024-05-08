<?php
session_start(); // Start the session

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['teacher_id'])) {
    header("Location: login_teacher.php");
    exit();
}

include './database/db.php'; // Include database connection
require 'vendor/autoload.php'; // Include the PhpSpreadsheet classes

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$message = ''; // Message to display after form handling

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['excelFile'])) {
        // Handle Excel file upload
        $fileMimeType = $_FILES['excelFile']['type'];
        if ($fileMimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($_FILES['excelFile']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            $conn->begin_transaction(); // Start transaction
            try {
                foreach ($sheetData as $row) {
                    if (!empty($row['A']) && !empty($row['B']) && !empty($row['C']) && !empty($row['D'])) { // Assuming A: Roll No, B: Batch, C: Test Name, D: Marks Obtained
                        $rollno = $row['A'];
                        $batch = $row['B'];
                        $testname = $row['C'];
                        $marks_obtained = $row['D'];
                        
                        // Fetch max marks and calculate percentage
                        $stmt = $conn->prepare("SELECT maxmarks FROM create_mains_test WHERE testname = ?");
                        $stmt->bind_param("s", $testname);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $data = $result->fetch_assoc();
                        if ($data) {
                            $max_marks = $data['maxmarks'];
                            $percentage = ($marks_obtained / $max_marks) * 100;

                            // Insert data into database
                            $insertSQL = $conn->prepare("INSERT INTO mains_test_score (rollno, batch, testname, max_marks, marks_obtained, percentage) VALUES (?, ?, ?, ?, ?, ?)");
                            $insertSQL->bind_param("sssidi", $rollno, $batch, $testname, $max_marks, $marks_obtained, $percentage);
                            $insertSQL->execute();
                            $insertSQL->close();
                        }
                    }
                }
                $conn->commit(); // Commit transaction
                $_SESSION['message']= "Data uploaded successfully!";
                // Redirect after form submission
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } catch (Exception $e) {
                $conn->rollback(); // Rollback transaction
                $_SESSION['message']= "Error: " . $e->getMessage();
            }
        } else {
            $message = "Invalid file type. Please upload an Excel file.";
        }
    } else {
        // Handle manual data input
        // Retrieve form data
        $rollno = $_POST['rollno'];
        $batch = $_POST['batch'];
        $testname = $_POST['testname'];
        $marks_obtained = $_POST['marks_obtained'];

        // Fetch max marks for the selected testname
        $stmt = $conn->prepare("SELECT maxmarks FROM create_mains_test WHERE testname = ?");
        $stmt->bind_param("s", $testname);
        $stmt->execute();
        $maxMarksResult = $stmt->get_result();
        if ($maxMarksResult && $maxMarksResult->num_rows > 0) {
            $row = $maxMarksResult->fetch_assoc();
            if ($row && isset($row['maxmarks'])) {
                $max_marks = $row['maxmarks'];

                // Ensure max_marks is not null and is a numeric value
                if ($max_marks !== null && is_numeric($max_marks) && is_numeric($marks_obtained)) {
                    $percentage = ($marks_obtained / $max_marks) * 100;

                    // Prepare and execute the insert statement...
                    $insertSQL = $conn->prepare("INSERT INTO mains_test_score (rollno, batch, testname, max_marks, marks_obtained, percentage) VALUES (?, ?, ?, ?, ?, ?)");
                    $insertSQL->bind_param("sssidi", $rollno, $batch, $testname, $max_marks, $marks_obtained, $percentage);
                    if ($insertSQL->execute()) {
                        $_SESSION['message'] = "New record created successfully";
                        // Redirect after form submission
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $_SESSION['message'] = "Error: " . $insertSQL->error;
                    }
                    $insertSQL->close();
                } else {
                    $_SESSION['message']= "Error: Invalid max marks or marks obtained. They must be numeric.";
                }
            } else {
                $_SESSION['message'] = "Max marks could not be found for the given test name.";
            }
        } else {
            $_SESSION['message']= "No record found for the provided test name, or the query failed.";
        }
        $stmt->close();
        
    }
  
}

// Fetch test names and max marks from create_mains_test table for the dropdown
$sql = "SELECT testname, maxmarks FROM create_mains_test";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Main Score Entry</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <script type="text/javascript">
        window.onload = function() {
            if (<?php echo json_encode(isset($_SESSION['message'])); ?>) {
                alert(<?php echo json_encode($_SESSION['message']); ?>);
                <?php unset($_SESSION['message']); ?>
            }
        };
    </script>
</head>
<body>
<?php include './includes/header.php'; ?>

<div class="container">
    <h4 class="mt-5">Enter Test Score</h4>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info"><?= $message; ?></div>
    <?php endif; ?>
    <form method="post" class="mt-4" enctype="multipart/form-data">
        <div class="form-group">
            Upload Excel File: <input type="file" name="excelFile" class="form-control">
            <input type="submit" value="Upload" class="btn btn-warning mt-3">
        </div>
    </form>

    <form method="post" class="mt-4">
        <div class="form-group">
            Roll No: <input type="text" name="rollno" class="form-control" required>
        </div>
        <div class="form-group">
            Batch: <input type="text" name="batch" class="form-control" required>
        </div>
        <div class="form-group">
            Test Name: <select name="testname" class="form-control" required>
                <?php while($row = $result->fetch_assoc()): ?>
                    <option value="<?= $row['testname']; ?>"><?= $row['testname']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            Marks Obtained: <input type="number" name="marks_obtained" class="form-control" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Submit" class="btn btn-warning">
        </div>
    </form>
</div>
<?php include './includes/footer.php'; ?>
</body>
</html>
