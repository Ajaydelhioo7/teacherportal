<?php
session_start();
session_destroy(); // Destroy all session data
header("Location: login_teacher.php"); // Redirect to login page
exit();
?>
