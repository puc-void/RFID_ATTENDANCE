<?php
session_start();
require 'connectDB.php';

if (!isset($_SESSION['searchQuery']) || empty($_SESSION['searchQuery'])) {
  $_SESSION['searchQuery'] = "1";
}

// Page load default (Today's logs)
if (isset($_POST['select_date']) && $_POST['select_date'] == 1) {
  $today = date("Y-m-d");
  $_SESSION['searchQuery'] = "checkindate='" . mysqli_real_escape_string($conn, $today) . "'";
}

// Log filter from modal
if (isset($_POST['log_date'])) {
  $_SESSION['searchQuery'] = "1";

  // Date range
  if (!empty($_POST['date_sel_start'])) {
    $Start_date = mysqli_real_escape_string($conn, $_POST['date_sel_start']);
    $_SESSION['searchQuery'] = "checkindate='" . $Start_date . "'";

    if (!empty($_POST['date_sel_end'])) {
      $End_date = mysqli_real_escape_string($conn, $_POST['date_sel_end']);
      $_SESSION['searchQuery'] = "checkindate BETWEEN '" . $Start_date . "' AND '" . $End_date . "'";
    }
  }

  // Time-In filter
  if (isset($_POST['time_sel']) && $_POST['time_sel'] == "Time_in") {
    if (!empty($_POST['time_sel_start']) && empty($_POST['time_sel_end'])) {
      $t_start = mysqli_real_escape_string($conn, $_POST['time_sel_start']);
      $_SESSION['searchQuery'] .= " AND timein='" . $t_start . "'";
    }
    if (!empty($_POST['time_sel_start']) && !empty($_POST['time_sel_end'])) {
      $t_start = mysqli_real_escape_string($conn, $_POST['time_sel_start']);
      $t_end   = mysqli_real_escape_string($conn, $_POST['time_sel_end']);
      $_SESSION['searchQuery'] .= " AND timein BETWEEN '" . $t_start . "' AND '" . $t_end . "'";
    }
  }

  // Time-Out filter
  if (isset($_POST['time_sel']) && $_POST['time_sel'] == "Time_out") {
    if (!empty($_POST['time_sel_start']) && empty($_POST['time_sel_end'])) {
      $t_start = mysqli_real_escape_string($conn, $_POST['time_sel_start']);
      $_SESSION['searchQuery'] .= " AND timeout='" . $t_start . "'";
    }
    if (!empty($_POST['time_sel_start']) && !empty($_POST['time_sel_end'])) {
      $t_start = mysqli_real_escape_string($conn, $_POST['time_sel_start']);
      $t_end   = mysqli_real_escape_string($conn, $_POST['time_sel_end']);
      $_SESSION['searchQuery'] .= " AND timeout BETWEEN '" . $t_start . "' AND '" . $t_end . "'";
    }
  }

  // User filter
  if (!empty($_POST['card_sel']) && $_POST['card_sel'] != '0') {
    $c_sel = mysqli_real_escape_string($conn, $_POST['card_sel']);
    $_SESSION['searchQuery'] .= " AND card_uid='" . $c_sel . "'";
  }

  // Device / Department filter
  $dev_filter = !empty($_POST['dev_uid']) ? $_POST['dev_uid'] : (!empty($_POST['dev_sel']) ? $_POST['dev_sel'] : '0');
  if ($dev_filter != '0') {
    $d_sel = mysqli_real_escape_string($conn, $dev_filter);
    $_SESSION['searchQuery'] .= " AND device_uid='" . $d_sel . "'";
  }
}

$sql = "SELECT *, (SELECT gender FROM users WHERE card_uid = users_logs.card_uid LIMIT 1) as gender FROM users_logs WHERE " . $_SESSION['searchQuery'] . " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="table-responsive" style="max-height: 600px;"> 
  <table class="table" id="attendanceLogsTable">
    <thead>
      <tr>
        <th>Log ID &amp; UID</th>
        <th>User Details</th>
        <th>Serial No</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Status</th>
        <th>Department</th>
      </tr>
    </thead>
    <tbody>
    <?php
      if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $initial = strtoupper(substr($row['username'] ?: 'U', 0, 1));
            $gender = $row['gender'] ?? 'N/A';
            $is_out = ($row['card_out'] == 1 || ($row['timeout'] != '00:00:00' && !empty($row['timeout'])));
      ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 8px;">
                  <span style="color: var(--text-subtle); font-size: 11.5px; font-weight: 600; min-width: 26px;">#<?php echo (int)$row['id']; ?></span>
                  <button type="button" class="uid-code" onclick="copyUID('<?php echo htmlspecialchars($row['card_uid'], ENT_QUOTES); ?>', event)" title="Click to copy Card UID">
                    <?php echo htmlspecialchars($row['card_uid']); ?>
                  </button>
                </div>
              </td>
              <td>
                <div class="user-id-badge">
                  <div class="user-avatar-bubble"><?php echo $initial; ?></div>
                  <div>
                    <div style="font-weight: 600; color: var(--text-foreground);"><?php echo htmlspecialchars($row['username']); ?></div>
                    <div style="font-size: 11.5px; color: var(--text-muted);"><?php echo htmlspecialchars($gender); ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-weight: 500; color: var(--text-foreground);">
                  <?php echo ($row['serialnumber'] != 0) ? htmlspecialchars($row['serialnumber']) : '---'; ?>
                </span>
              </td>
              <td style="color: var(--text-muted); font-size: 13px;"><?php echo htmlspecialchars($row['checkindate']); ?></td>
              <td>
                <span class="time-badge time-in">
                  <i class="fa fa-sign-in"></i> <?php echo htmlspecialchars($row['timein']); ?>
                </span>
              </td>
              <td>
                <?php if ($is_out): ?>
                  <span class="time-badge time-out">
                    <i class="fa fa-sign-out"></i> <?php echo htmlspecialchars($row['timeout']); ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--text-subtle);font-size:12px;">— Active —</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($is_out): ?>
                  <span class="shadcn-badge" style="background:var(--bg-muted);color:var(--text-secondary);border:1px solid var(--border-color);">
                    Completed
                  </span>
                <?php else: ?>
                  <span class="shadcn-badge success">
                    <i class="fa fa-circle" style="font-size:7px;"></i> Inside
                  </span>
                <?php endif; ?>
              </td>
              <td>
                <span class="dept-badge">
                  <?php echo ($row['device_dep'] == "0" || empty($row['device_dep'])) ? "All" : htmlspecialchars($row['device_dep']); ?>
                </span>
              </td>
            </tr>
      <?php
        }
      } else {
        echo '<tr><td colspan="8" class="empty-state" style="text-align:center; padding:52px 20px; color:var(--text-muted);">No attendance logs found for the selected filter criteria.</td></tr>';
      }
    ?>
    </tbody>
  </table>
</div>
