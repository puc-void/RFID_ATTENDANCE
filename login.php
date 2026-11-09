<?php
session_start();
if (isset($_SESSION['Admin-name'])) {
  header("location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign In — RFID Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Admin login for RFID Attendance Management System">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <script>
      (function() {
        var savedTheme = localStorage.getItem('rfid_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
      })();
    </script>
    <script src="js/jquery-2.2.3.min.js"></script>
    <script>
      $(document).ready(function(){
        $(document).on('click', '.message a.toggle-reset', function(e){
          e.preventDefault();
          $('form').animate({height: "toggle", opacity: "toggle"}, 250);
          $('h1').animate({height: "toggle", opacity: "toggle"}, 250);
        });
      });

      function toggleLoginTheme() {
        var current = document.documentElement.getAttribute('data-theme') || 'light';
        var next = (current === 'dark') ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('rfid_theme', next);
        var icon = document.getElementById('loginThemeIcon');
        if (icon) icon.className = (next === 'dark') ? 'fa fa-sun-o' : 'fa fa-moon-o';
      }
    </script>
</head>
<body>

<header class="login-header">
  <a href="login.php" class="login-brand">
    <span class="login-brand-icon"><i class="fa fa-id-card-o"></i></span>
    <span class="brand-stroke-text">RFID ATTENDANCE</span>
  </a>
  <div class="header-actions">
    <a href="create_admin.php" class="header-link"><i class="fa fa-user-plus"></i> Create Admin</a>
    <button type="button" class="theme-btn" onclick="toggleLoginTheme()" title="Switch Light / Dark Theme">
      <i class="fa fa-moon-o" id="loginThemeIcon"></i>
    </button>
  </div>
</header>

<main>
  <div class="login-wrapper">
    <div class="form">
      <div class="auth-heading">
        <h1>Sign in to Dashboard</h1>
        <h1 id="reset">Reset Password</h1>
        <p>Enter your administrator credentials to manage RFID attendance</p>
      </div>

      <?php
        if (isset($_GET['error'])) {
          if ($_GET['error'] == "invalidEmail") {
              echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <span><strong>Invalid email address.</strong> Please check and try again.</span></div>';
          } elseif ($_GET['error'] == "sqlerror") {
              echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <span><strong>Database error.</strong> Please try again later.</span></div>';
          } elseif ($_GET['error'] == "wrongpassword") {
              echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <span><strong>Incorrect password.</strong> Please try again.</span></div>';
          } elseif ($_GET['error'] == "nouser") {
              echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <span><strong>Account not found.</strong> No admin exists with that email.</span></div>';
          }
        }
        if (isset($_GET['reset']) && $_GET['reset'] == "success") {
          echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> <span>Check your email for the password reset link.</span></div>';
        }
        if (isset($_GET['account']) && $_GET['account'] == "activated") {
          echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> <span>Account activated. Please log in.</span></div>';
        }
        if (isset($_GET['active']) && $_GET['active'] == "success") {
          echo '<div class="alert alert-success"><i class="fa fa-check-circle"></i> <span>Activation link sent to your email.</span></div>';
        }
      ?>

      <!-- Password Reset Form -->
      <form class="reset-form" action="reset_pass.php" method="post" enctype="multipart/form-data">
        <label for="reset_email">Admin Email Address</label>
        <input type="email" name="email" id="reset_email" placeholder="admin@gmail.com" required/>
        <button type="submit" name="reset_pass">Send Reset Link</button>
        <p class="message"><a href="#" class="toggle-reset">&larr; Back to Sign In</a></p>
      </form>

      <!-- Sign In Form -->
      <form class="login-form" action="ac_login.php" method="post" enctype="multipart/form-data">
        <label for="email">Email Address</label>
        <input type="email" name="email" id="email" placeholder="admin@gmail.com" required/>
        <label for="pwd">Password</label>
        <input type="password" name="pwd" id="pwd" placeholder="Enter your password" required/>
        <button type="submit" name="login" id="login"><i class="fa fa-sign-in"></i> Sign In</button>
        <p class="message">Forgot your password? <a href="#" class="toggle-reset">Reset it here</a></p>
        <p class="message" style="margin-top:8px;">Need an admin account? <a href="create_admin.php">Create Admin</a></p>
      </form>
    </div>
  </div>
</main>
<script>
  var cur = document.documentElement.getAttribute('data-theme') || 'light';
  var ic = document.getElementById('loginThemeIcon');
  if (ic) ic.className = (cur === 'dark') ? 'fa fa-sun-o' : 'fa fa-moon-o';
</script>
</body>
</html>
