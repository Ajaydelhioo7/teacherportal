<?php
// Get the root URL of the website
$rootUrl = (isset($_SERVER['HTTPS']) ? "http://localhost/teacher/" : "http://") . $_SERVER['HTTP_HOST'];

// If your application is in a subfolder, append the folder name to the root URL
// For example, if your app is located in the 'myapp' folder, uncomment the line below and replace 'myapp' with the actual folder name
// $rootUrl .= '/myapp';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title>
    99notes Student Portal
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="<?php echo $rootUrl; ?>/student/assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="<?php echo $rootUrl; ?>/student/assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="/teacher/assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="<?php echo $rootUrl; ?>/teacher/assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />
  <style>
    .logoimg{
      max-height:300px!important;
      height: 74px;
    width: 200px;
    }
    .mysidebar{
      height: 100vh!important;
    overflow-y: hidden!important;
    }
  </style>
 
</head>

<body class="g-sidenav-show  bg-gray-100">
    <?php include('sidebar.php')?>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <?php include('navbar.php')?>
    <div class="container-fluid py-4">