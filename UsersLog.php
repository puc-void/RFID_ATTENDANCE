<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
  exit();
}
require 'connectDB.php';

// Today's attendance stats
$today = date("Y-m-d");
$today_total = 0;
$currently_in = 0;
$checked_out = 0;
$active_readers = 0;

$log_stat_q = mysqli_query($conn, "SELECT 
  COUNT(*) as total_today,
  SUM(CASE WHEN card_out=0 THEN 1 ELSE 0 END) as in_now,
  SUM(CASE WHEN card_out=1 THEN 1 ELSE 0 END) as out_done,
  COUNT(DISTINCT device_dep) as active_depts
  FROM users_logs WHERE checkindate='$today'");
if ($log_stat_q && $lrow = mysqli_fetch_assoc($log_stat_q)) {
  $today_total    = (int)$lrow['total_today'];
  $currently_in   = (int)$lrow['in_now'];
  $checked_out    = (int)$lrow['out_done'];
  $active_readers = (int)$lrow['active_depts'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Attendance Logs — RFID Attendance</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="css/userslog.css">

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.js"
            integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
            crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="js/bootbox.min.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script src="js/user_log.js"></script>
    <script>
      function loadLogsTable(selectToday) {
        $.ajax({
          url: "user_log_up.php",
          type: 'POST',
          data: { 'select_date': selectToday ? 1 : 0 }
        }).done(function(data) {
          $('#userslog').html(data);
          var q = $('#logSearchInput').val();
          if (q) filterTableRows(q, '#attendanceLogsTable');
        });
      }

      $(document).ready(function(){
        loadLogsTable(true);
        setInterval(function(){
          loadLogsTable(false);
        }, 5000);
      });
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1>Attendance <span class="accent">Daily Logs</span></h1>
      <p class="page-subtitle">Real-time check-in &amp; check-out records with advanced filtering and Excel export</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <div class="stat-badge">
        <span class="dot"></span>
        Live Feed (5s)
      </div>
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#Filter-export">
        <i class="fa fa-sliders"></i> Filter / Export Excel
      </button>
    </div>
  </div>

  <!-- shadcn KPI Stat Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Today's Total Scans</span>
        <span class="stat-value"><?php echo $today_total; ?></span>
        <span class="stat-sub">Date: <?php echo htmlspecialchars($today); ?></span>
      </div>
      <div class="stat-icon indigo"><i class="fa fa-calendar-check-o"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Currently Checked In</span>
        <span class="stat-value"><?php echo $currently_in; ?></span>
        <span class="stat-sub">Active inside premises</span>
      </div>
      <div class="stat-icon emerald"><i class="fa fa-sign-in"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Checked Out Today</span>
        <span class="stat-value"><?php echo $checked_out; ?></span>
        <span class="stat-sub">Completed attendance</span>
      </div>
      <div class="stat-icon rose"><i class="fa fa-sign-out"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Active Departments</span>
        <span class="stat-value"><?php echo $active_readers; ?></span>
        <span class="stat-sub">Departments with logs today</span>
      </div>
      <div class="stat-icon sky"><i class="fa fa-building-o"></i></div>
    </div>
  </div>

  <!-- Logs Table Card -->
  <div class="table-card">
    <div class="toolbar-wrap">
      <div class="search-box-wrap">
        <i class="fa fa-search search-icon"></i>
        <input type="text" id="logSearchInput" class="search-input" placeholder="Search logs by name, serial no, UID, time, department..." oninput="handleLogSearch(this.value)">
        <button type="button" id="logSearchClear" class="search-clear-btn" onclick="clearLogSearch()">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="actions-wrap">
        <button type="button" class="action-btn-sm" onclick="resetLogsToToday()">
          <i class="fa fa-calendar-o"></i> Today's Logs
        </button>
        <button type="button" class="action-btn-sm" onclick="window.print()">
          <i class="fa fa-print"></i> Print Report
        </button>
        <button type="button" class="action-btn-sm" data-toggle="modal" data-target="#Filter-export">
          <i class="fa fa-file-excel-o" style="color:var(--success);"></i> Export Excel
        </button>
      </div>
    </div>

    <div id="userslog"></div>
  </div>

  <!-- Log Filter & Export Modal -->
  <div class="modal fade bd-example-modal-lg" id="Filter-export" tabindex="-1" role="dialog" aria-labelledby="Filter/Export" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 680px;" role="document">
      <div class="modal-content animate">
        <div class="modal-header">
          <h3 class="modal-title" id="exampleModalLongTitle">
            <span class="modal-title-icon"><i class="fa fa-filter"></i></span>
            Filter Attendance Logs &amp; Export
          </h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form method="POST" action="Export_Excel.php" enctype="multipart/form-data">
          <div class="modal-body">
            <div class="filter-grid">
              <div class="filter-card-section">
                <div class="filter-card-heading">
                  <span><i class="fa fa-calendar" style="color:var(--primary);"></i> Date Range</span>
                </div>
                <label for="date_sel_start">Start Date</label>
                <input type="date" name="date_sel_start" id="date_sel_start">
                <label for="date_sel_end">End Date</label>
                <input type="date" name="date_sel_end" id="date_sel_end" style="margin-bottom:0;">
              </div>

              <div class="filter-card-section">
                <div class="filter-card-heading">
                  <span><i class="fa fa-clock-o" style="color:var(--primary);"></i> Time Range</span>
                  <div class="time-toggle-pills">
                    <input type="radio" id="radio-one" name="time_sel" class="time_sel" value="Time_in" checked/>
                    <label for="radio-one">Time-In</label>
                    <input type="radio" id="radio-two" name="time_sel" class="time_sel" value="Time_out" />
                    <label for="radio-two">Time-Out</label>
                  </div>
                </div>
                <label for="time_sel_start">From Time</label>
                <input type="time" name="time_sel_start" id="time_sel_start">
                <label for="time_sel_end">To Time</label>
                <input type="time" name="time_sel_end" id="time_sel_end" style="margin-bottom:0;">
              </div>
            </div>

            <div class="filter-select-row">
              <div>
                <label for="card_sel">Filter by User</label>
                <select class="card_sel" name="card_sel" id="card_sel" style="margin-bottom:0;">
                  <option value="0">All Registered Users</option>
                  <?php
                    $sql = "SELECT * FROM users WHERE add_card=1 ORDER BY username ASC";
                    $result = mysqli_stmt_init($conn);
                    if (mysqli_stmt_prepare($result, $sql)) {
                        mysqli_stmt_execute($result);
                        $resultl = mysqli_stmt_get_result($result);
                        while ($row = mysqli_fetch_assoc($resultl)) {
                            echo '<option value="'.htmlspecialchars($row['card_uid']).'">'.htmlspecialchars($row['username']).' ('.htmlspecialchars($row['card_uid']).')</option>';
                        }
                    }
                  ?>
                </select>
              </div>

              <div>
                <label for="dev_sel">Filter by Department</label>
                <select class="dev_sel" name="dev_sel" id="dev_sel" style="margin-bottom:0;">
                  <option value="0">All Departments</option>
                  <?php
                    $sql = "SELECT * FROM devices ORDER BY device_dep ASC";
                    $result = mysqli_stmt_init($conn);
                    if (mysqli_stmt_prepare($result, $sql)) {
                        mysqli_stmt_execute($result);
                        $resultl = mysqli_stmt_get_result($result);
                        while ($row = mysqli_fetch_assoc($resultl)) {
                            echo '<option value="'.htmlspecialchars($row['device_uid']).'">'.htmlspecialchars($row['device_dep']).'</option>';
                        }
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" name="To_Excel" value="Export" class="btn btn-success">
              <i class="fa fa-download"></i> Export to Excel
            </button>
            <button type="button" name="user_log" id="user_log" class="btn btn-primary">
              <i class="fa fa-check"></i> Apply Filter
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</main>
<script>
  function handleLogSearch(val) {
    var btn = document.getElementById('logSearchClear');
    if (btn) btn.style.display = val.length > 0 ? 'block' : 'none';
    filterTableRows(val, '#attendanceLogsTable');
  }
  function clearLogSearch() {
    var inp = document.getElementById('logSearchInput');
    if (inp) {
      inp.value = '';
      handleLogSearch('');
      inp.focus();
    }
  }
  function resetLogsToToday() {
    loadLogsTable(true);
    showToast("Filter Reset", "Showing today's attendance records.", "info", 2400);
  }
</script>
</body>
</html>
