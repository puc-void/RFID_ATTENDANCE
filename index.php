<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Users — RFID Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RFID Attendance Management — Registered Users">
    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <link rel="stylesheet" type="text/css" href="css/Users.css">
    <script>
      $(window).on("load resize", function() {
        var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
        $('.tbl-header').css({'padding-right': scrollWidth});
      }).resize();
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
<div class="page-wrapper">
  <div class="page-header slideInDown animated">
    <div>
      <h1>Registered <span class="accent">Users</span></h1>
      <p class="page-subtitle">All users with active RFID cards</p>
    </div>
    <div class="stat-badge">
      <span class="dot"></span>
      Live Database
    </div>
  </div>

  <section>
    <div class="table-card slideInRight animated">
      <div class="table-responsive" style="max-height: 520px;">
        <table class="table">
          <thead>
            <tr>
              <th>User</th>
              <th>Serial Number</th>
              <th>Gender</th>
              <th>Card UID</th>
              <th>Date Added</th>
              <th>Department</th>
            </tr>
          </thead>
          <tbody>
            <?php
              require 'connectDB.php';
              $sql = "SELECT * FROM users WHERE add_card=1 ORDER BY id DESC";
              $result = mysqli_stmt_init($conn);
              if (!mysqli_stmt_prepare($result, $sql)) {
                  echo '<tr><td colspan="6" class="empty-state"><p class="error">SQL Error</p></td></tr>';
              } else {
                  mysqli_stmt_execute($result);
                  $resultl = mysqli_stmt_get_result($result);
                  if (mysqli_num_rows($resultl) > 0) {
                      while ($row = mysqli_fetch_assoc($resultl)) {
                          $initial = strtoupper(substr($row['username'], 0, 1));
                          $gender_class = strtolower($row['gender']) === 'female' ? 'female' : 'male';
            ?>
              <tr>
                <td>
                  <div class="user-id-badge">
                    <div class="avatar-circle"><?php echo $initial; ?></div>
                    <div>
                      <div><?php echo htmlspecialchars($row['username']); ?></div>
                      <div style="font-size:11px;color:#4a5568;">ID: <?php echo $row['id']; ?></div>
                    </div>
                  </div>
                </td>
                <td><span style="font-weight: 500;"><?php echo ($row['serialnumber'] != 0) ? htmlspecialchars($row['serialnumber']) : '---'; ?></span></td>
                <td><span class="gender-pill <?php echo $gender_class; ?>"><?php echo $row['gender']; ?></span></td>
                <td><span class="uid-code"><?php echo htmlspecialchars($row['card_uid']); ?></span></td>
                <td><?php echo htmlspecialchars($row['user_date']); ?></td>
                <td><span class="dept-badge">⬡ <?php echo htmlspecialchars($row['device_dep']); ?></span></td>
              </tr>
            <?php
                      }
                  } else {
                      echo '<tr><td colspan="6" style="text-align:center;padding:60px 20px;color:#4a5568;">No registered users found.</td></tr>';
                  }
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>
</main>
</body>
</html>