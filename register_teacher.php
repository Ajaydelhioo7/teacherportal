<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration</title>
    <link rel="stylesheet" href="../css/register_teacher.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include './includes/header.php'; ?>
<div class="container">
    <h4 class="mb-4">Register Teacher</h4>
    <form action="register_teacher.php" method="post" class="needs-validation" novalidate>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" required>
            <div class="invalid-feedback">
                Please enter a username.
            </div>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="invalid-feedback">
                Please enter a password.
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
            <div class="invalid-feedback">
                Please enter a valid email address.
            </div>
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" class="form-control" id="phone" name="phone" required>
            <div class="invalid-feedback">
                Please enter a phone number.
            </div>
        </div>
        <button type="submit" name="submit" class="btn btn-warning">Register</button>
        
    </form>
</div>

    
    <?php
    // Include the database connection file
    include './database/db.php';

    // Check if the form is submitted
    if (isset($_POST['submit'])) {
        // retrieve the form data by using the element's name attributes value as key
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash the password for security
        $email = $_POST['email'];
        $phone = $_POST['phone'];

        // prepare and bind
        $stmt = $conn->prepare("INSERT INTO Teachers (username, password, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $email, $phone);

        // execute and check errors
        if ($stmt->execute()) {
            echo "New teacher registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
    ?>
    <script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
      'use strict';
      window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
          form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);
    })();
</script>
    <?php include './includes/footer.php'; ?>
</body>
</html>
