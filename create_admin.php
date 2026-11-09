<?php
session_start();
require 'connectDB.php';

$adminCount = 0;
$count = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM admin');
if ($count) {
    $adminCount = (int) mysqli_fetch_assoc($count)['total'];
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!preg_match('/^[a-zA-Z][a-zA-Z ]{1,29}$/', $name)) {
        $error = 'Use 2–30 letters for the administrator name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Use a password with at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'The passwords do not match.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM admin WHERE admin_email = ?');
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        if (mysqli_num_rows(mysqli_stmt_get_result($check)) > 0) {
            $error = 'An administrator already uses this email address.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $add = mysqli_prepare($conn, 'INSERT INTO admin (admin_name, admin_email, admin_pwd) VALUES (?, ?, ?)');
            mysqli_stmt_bind_param($add, 'sss', $name, $email, $hash);
            if (mysqli_stmt_execute($add)) {
                $_SESSION['Admin-name'] = $name;
                $_SESSION['Admin-email'] = $email;
                header('Location: index.php?login=success');
                exit;
            }
            $error = 'Unable to create the account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Admin — RFID Attendance</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="css/login.css">
  <script>
    (function() {
      var savedTheme = localStorage.getItem('rfid_theme') || 'light';
      document.documentElement.setAttribute('data-theme', savedTheme);
    })();

    function toggleAuthTheme() {
      var current = document.documentElement.getAttribute('data-theme') || 'light';
      var next = (current === 'dark') ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem('rfid_theme', next);
      var icon = document.getElementById('authThemeIcon');
      if (icon) icon.className = (next === 'dark') ? 'fa fa-sun-o' : 'fa fa-moon-o';
    }
  </script>
</head>
<body>

<header class="auth-header">
  <a class="auth-brand" href="login.php">
    <span class="auth-brand-icon"><i class="fa fa-id-card-o"></i></span>
    <span class="brand-stroke-text">RFID ATTENDANCE</span>
  </a>
  <div class="header-actions">
    <a class="header-link" href="login.php"><i class="fa fa-sign-in"></i> Sign In</a>
    <button type="button" class="theme-btn" onclick="toggleAuthTheme()" title="Switch Light / Dark Theme">
      <i class="fa fa-moon-o" id="authThemeIcon"></i>
    </button>
  </div>
</header>

<main>
  <div class="auth-shell">
    <aside class="auth-intro">
      <span class="eyebrow"><i class="fa fa-shield"></i> ADMINISTRATION</span>
      <h1>Set up your workspace.</h1>
      <p>Create an administrator account to manage ESP32 RFID readers, enroll student &amp; staff cards, and configure real-time attendance alerts.</p>
      <div class="auth-feature-list">
        <div class="auth-feature-item">
          <i class="fa fa-id-card-o"></i>
          <span>Instant RFID card enrollment &amp; department assignment</span>
        </div>
        <div class="auth-feature-item">
          <i class="fa fa-paper-plane-o"></i>
          <span>Real-time Telegram check-in &amp; check-out notifications</span>
        </div>
        <div class="auth-feature-item">
          <i class="fa fa-user-circle-o"></i>
          <span>Currently registered admins: <strong><?php echo $adminCount; ?></strong></span>
        </div>
      </div>
    </aside>

    <div class="auth-card">
      <h2>Create Administrator</h2>
      <p class="auth-copy">All fields are required. You will be signed in automatically.</p>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fa fa-exclamation-circle"></i>
          <span><?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form class="login-form" method="post">
        <label for="name">Full Name</label>
        <input id="name" name="name" maxlength="30" placeholder="Administrator name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">

        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" minlength="8" placeholder="At least 8 characters" required>

        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password" name="confirm_password" minlength="8" placeholder="Re-enter your password" required>

        <button type="submit">
          <i class="fa fa-user-plus"></i> Create Admin Account
        </button>

        <p class="message">Already have an account? <a href="login.php">Sign in here</a></p>
      </form>
    </div>
  </div>
</main>

<script>
  var cur = document.documentElement.getAttribute('data-theme') || 'light';
  var ic = document.getElementById('authThemeIcon');
  if (ic) ic.className = (cur === 'dark') ? 'fa fa-sun-o' : 'fa fa-moon-o';
</script>
</body>
</html>
