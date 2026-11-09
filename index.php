<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
  exit();
}
require 'connectDB.php';

// Fetch Dashboard Stats
$total_users = 0;
$pending_cards = 0;
$male_count = 0;
$female_count = 0;
$dept_count = 0;

$stat_q = mysqli_query($conn, "SELECT 
  SUM(CASE WHEN add_card=1 THEN 1 ELSE 0 END) as active_users,
  SUM(CASE WHEN add_card=0 THEN 1 ELSE 0 END) as pending_cards,
  SUM(CASE WHEN add_card=1 AND LOWER(gender)='male' THEN 1 ELSE 0 END) as males,
  SUM(CASE WHEN add_card=1 AND LOWER(gender)='female' THEN 1 ELSE 0 END) as females,
  COUNT(DISTINCT CASE WHEN add_card=1 AND device_dep!='0' AND device_dep!='' THEN device_dep END) as depts
  FROM users");
if ($stat_q && $stat_row = mysqli_fetch_assoc($stat_q)) {
  $total_users   = (int)$stat_row['active_users'];
  $pending_cards = (int)$stat_row['pending_cards'];
  $male_count    = (int)$stat_row['males'];
  $female_count  = (int)$stat_row['females'];
  $dept_count    = (int)$stat_row['depts'];
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
</head>
<body>
<?php include 'header.php'; ?>
<main>
<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1>Registered <span class="accent">Users</span></h1>
      <p class="page-subtitle">Directory of all enrolled students &amp; staff with active RFID cards</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <div class="stat-badge">
        <span class="dot"></span>
        System Online
      </div>
      <a href="ManageUsers.php" class="btn btn-primary">
        <i class="fa fa-user-plus"></i> Manage Users
      </a>
    </div>
  </div>

  <!-- shadcn KPI Stat Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Registered Users</span>
        <span class="stat-value"><?php echo $total_users; ?></span>
        <span class="stat-sub">Active RFID cards assigned</span>
      </div>
      <div class="stat-icon indigo"><i class="fa fa-users"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Unassigned Scans</span>
        <span class="stat-value"><?php echo $pending_cards; ?></span>
        <span class="stat-sub">Cards awaiting registration</span>
      </div>
      <div class="stat-icon amber"><i class="fa fa-id-badge"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Gender Breakdown</span>
        <span class="stat-value"><?php echo $male_count; ?> <span style="font-size:14px;color:var(--text-muted);font-weight:500;">M</span> / <?php echo $female_count; ?> <span style="font-size:14px;color:var(--text-muted);font-weight:500;">F</span></span>
        <span class="stat-sub">Male &amp; Female distribution</span>
      </div>
      <div class="stat-icon sky"><i class="fa fa-pie-chart"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Active Departments</span>
        <span class="stat-value"><?php echo max(1, $dept_count); ?></span>
        <span class="stat-sub">Assigned reader zones</span>
      </div>
      <div class="stat-icon purple"><i class="fa fa-building-o"></i></div>
    </div>
  </div>

  <section>
    <div class="table-card">
      <!-- shadcn Search & Action Toolbar -->
      <div class="toolbar-wrap">
        <div class="search-box-wrap">
          <i class="fa fa-search search-icon"></i>
          <input type="text" id="usersSearchInput" class="search-input" placeholder="Search by name, serial no, UID, department..." oninput="handleUsersSearch(this.value)">
          <button type="button" id="usersSearchClear" class="search-clear-btn" onclick="clearUsersSearch()">
            <i class="fa fa-times"></i>
          </button>
        </div>
        <div class="actions-wrap">
          <button type="button" class="action-btn-sm" onclick="window.print()" title="Print User Directory">
            <i class="fa fa-print"></i> Print
          </button>
          <a href="UsersLog.php" class="action-btn-sm">
            <i class="fa fa-clock-o"></i> View Logs
          </a>
        </div>
      </div>

      <div class="table-responsive" style="max-height: 540px;">
        <table class="table" id="registeredUsersTable">
          <thead>
            <tr>
              <th>User</th>
              <th>Serial Number</th>
              <th>Gender</th>
              <th>Card UID</th>
              <th>Date Added</th>
              <th>Department</th>
              <th style="text-align:right !important;">Details</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT * FROM users WHERE add_card=1 ORDER BY id DESC";
              $result = mysqli_stmt_init($conn);
              if (!mysqli_stmt_prepare($result, $sql)) {
                  echo '<tr><td colspan="7" class="empty-state" style="text-align:center;padding:40px;color:var(--danger);">SQL Error</td></tr>';
              } else {
                  mysqli_stmt_execute($result);
                  $resultl = mysqli_stmt_get_result($result);
                  if (mysqli_num_rows($resultl) > 0) {
                      while ($row = mysqli_fetch_assoc($resultl)) {
                          $initial = strtoupper(substr($row['username'] ?: 'U', 0, 1));
                          $gender_class = strtolower($row['gender']) === 'female' ? 'female' : 'male';
                          $serial_display = ($row['serialnumber'] != 0) ? htmlspecialchars($row['serialnumber']) : '---';
                          $phone_display = (!empty($row['phone']) && $row['phone'] != 'None') ? htmlspecialchars($row['phone']) : 'N/A';
                          $email_display = (!empty($row['email']) && $row['email'] != 'None') ? htmlspecialchars($row['email']) : 'No email';
                          $dept_display = ($row['device_dep'] == '0' || empty($row['device_dep'])) ? 'All Departments' : htmlspecialchars($row['device_dep']);
            ?>
              <tr>
                <td>
                  <div class="user-id-badge">
                    <div class="avatar-circle"><?php echo $initial; ?></div>
                    <div>
                      <div class="user-name-main"><?php echo htmlspecialchars($row['username']); ?></div>
                      <div class="user-meta-sub">ID #<?php echo (int)$row['id']; ?> &middot; <?php echo ($phone_display !== 'N/A' ? $phone_display . ' &middot; ' : '') . $email_display; ?></div>
                    </div>
                  </div>
                </td>
                <td><span style="font-weight: 500;"><?php echo $serial_display; ?></span></td>
                <td><span class="gender-pill <?php echo $gender_class; ?>"><?php echo htmlspecialchars($row['gender']); ?></span></td>
                <td>
                  <button type="button" class="uid-code" onclick="copyUID('<?php echo htmlspecialchars($row['card_uid'], ENT_QUOTES); ?>', event)" title="Click to copy Card UID">
                    <i class="fa fa-credit-card" style="font-size:11px;opacity:0.7;"></i>
                    <?php echo htmlspecialchars($row['card_uid']); ?>
                    <i class="fa fa-clone uid-copy-hint"></i>
                  </button>
                </td>
                <td style="color:var(--text-muted);"><?php echo htmlspecialchars($row['user_date']); ?></td>
                <td><span class="dept-badge"><i class="fa fa-building-o"></i> <?php echo $dept_display; ?></span></td>
                <td style="text-align:right;">
                  <button type="button" class="action-btn-sm"
                    onclick="openUserDetailModal(<?php echo htmlspecialchars(json_encode([
                      'id' => $row['id'],
                      'username' => $row['username'],
                      'serial' => $serial_display,
                      'gender' => $row['gender'],
                      'phone' => $phone_display,
                      'email' => $email_display,
                      'uid' => $row['card_uid'],
                      'date' => $row['user_date'],
                      'dept' => $dept_display
                    ]), ENT_QUOTES); ?>)">
                    <i class="fa fa-eye"></i> View
                  </button>
                </td>
              </tr>
            <?php
                      }
                  } else {
                      echo '<tr><td colspan="7" class="empty-state" style="text-align:center;padding:56px 20px;color:var(--text-muted);">No registered users found. Go to <a href="ManageUsers.php" style="color:var(--primary);font-weight:600;">Manage Users</a> to enroll cards.</td></tr>';
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

<!-- User Quick Details Modal -->
<div class="modal fade" id="userDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;" role="document">
    <div class="modal-content animate">
      <div class="modal-header">
        <h3 class="modal-title">
          <span class="modal-title-icon"><i class="fa fa-id-card-o"></i></span>
          <span id="detailModalName">User Profile</span>
        </h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="detail-grid">
          <div class="detail-item">
            <div class="detail-item-label">Full Name</div>
            <div class="detail-item-value" id="detailName">—</div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Serial Number</div>
            <div class="detail-item-value" id="detailSerial">—</div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Card UID</div>
            <div class="detail-item-value" id="detailUID" style="font-family:'JetBrains Mono',monospace;color:var(--primary);">—</div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Department</div>
            <div class="detail-item-value" id="detailDept">—</div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Phone &amp; Email</div>
            <div class="detail-item-value" id="detailEmail">—</div>
          </div>
          <div class="detail-item">
            <div class="detail-item-label">Gender &amp; Enrolled Date</div>
            <div class="detail-item-value" id="detailMeta">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <a href="ManageUsers.php" class="btn btn-primary"><i class="fa fa-pencil"></i> Edit in Manage Users</a>
      </div>
    </div>
  </div>
</div>

<script>
  function handleUsersSearch(val) {
    var clearBtn = document.getElementById('usersSearchClear');
    if (clearBtn) clearBtn.style.display = val.length > 0 ? 'block' : 'none';
    filterTableRows(val, '#registeredUsersTable');
  }

  function clearUsersSearch() {
    var input = document.getElementById('usersSearchInput');
    if (input) {
      input.value = '';
      handleUsersSearch('');
      input.focus();
    }
  }

  function openUserDetailModal(user) {
    document.getElementById('detailModalName').textContent = user.username || 'User Profile';
    document.getElementById('detailName').textContent = user.username || '—';
    document.getElementById('detailSerial').textContent = user.serial || '—';
    document.getElementById('detailUID').textContent = user.uid || '—';
    document.getElementById('detailDept').textContent = user.dept || '—';
    document.getElementById('detailEmail').textContent = ((user.phone && user.phone !== 'N/A') ? user.phone + ' • ' : '') + (user.email || '—');
    document.getElementById('detailMeta').textContent = (user.gender || '—') + ' • ' + (user.date || '—');
    $('#userDetailModal').modal('show');
  }
</script>
</body>
</html>
