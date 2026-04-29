<?php
session_start();
if (isset($_SESSION['Admin-name'])) {
  header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Log In — RFID Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Admin login for RFID Attendance Management System">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="js/jquery-2.2.3.min.js"></script>
    <script>
      $(document).ready(function(){
        $(document).on('click', '.message a', function(){
          $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
          $('h1').animate({height: "toggle", opacity: "toggle"}, "slow");
        });
      });
    </script>
</head>
<body>

<!-- Minimal branding header -->
<header style="display:flex;align-items:center;justify-content:center;padding:24px 32px;border-bottom:1px solid rgba(255,255,255,0.05);">
  <a href="login.php" style="font-family:'Outfit',sans-serif;font-size:22px;font-weight:700;color:#00F0FF;text-decoration:none;display:flex;align-items:center;gap:8px;">
    <span style="font-size:20px;filter:drop-shadow(0 0 8px rgba(0,240,255,0.5));">⬡</span>
    RFID Attendance
  </a>
</header>

<main>
  <div class="slideInDown animated">
    <h1>Welcome back, Admin</h1>
    <h1 id="reset">Password Reset</h1>

    <div class="login-page">
      <div class="form">
        <?php
          if (isset($_GET['error'])) {
            if ($_GET['error'] == "invalidEmail") {
                echo '<div class="alert alert-danger">
                        <strong>Invalid email address.</strong> Please check and try again.
                      </div>';
            }
            elseif ($_GET['error'] == "sqlerror") {
                echo '<div class="alert alert-danger">
                        <strong>Database error.</strong> Please try again later.
                      </div>';
            }
            elseif ($_GET['error'] == "wrongpassword") {
                echo '<div class="alert alert-danger">
                        <strong>Incorrect password.</strong> Please try again.
                      </div>';
            }
            elseif ($_GET['error'] == "nouser") {
                echo '<div class="alert alert-danger">
                        <strong>Email not found.</strong> No admin account exists with that email.
                      </div>';
            }
          }
          if (isset($_GET['reset'])) {
            if ($_GET['reset'] == "success") {
                echo '<div class="alert alert-success">
                        Check your email for the reset link.
                      </div>';
            }
          }
          if (isset($_GET['account'])) {
            if ($_GET['account'] == "activated") {
                echo '<div class="alert alert-success">
                        Account activated. Please log in.
                      </div>';
            }
          }
          if (isset($_GET['active'])) {
            if ($_GET['active'] == "success") {
                echo '<div class="alert alert-success">
                        Activation link sent to your email.
                      </div>';
            }
          }
        ?>
        <div class="alert1"></div>

        <!-- Reset form (hidden by default) -->
        <form class="reset-form" action="reset_pass.php" method="post" enctype="multipart/form-data">
          <input type="email" name="email" placeholder="Enter your email address..." required/>
          <button type="submit" name="reset_pass">Send Reset Link</button>
          <p class="message"><a href="#">← Back to Login</a></p>
        </form>

        <!-- Login form -->
        <form class="login-form" action="ac_login.php" method="post" enctype="multipart/form-data">
          <input type="email" name="email" id="email" placeholder="Admin email address..." required/>
          <input type="password" name="pwd" id="pwd" placeholder="Password..." required/>
          <button type="submit" name="login" id="login">Sign In</button>
          <p class="message">Forgot your password? <a href="#">Reset it here</a></p>
        </form>
      </div>
    </div>
  </div>
</main>

</body>
</html>
