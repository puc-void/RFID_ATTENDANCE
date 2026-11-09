<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
    header("location: login.php");
    exit();
}
require 'connectDB.php';

$total_devs = 0;
$att_devs = 0;
$enr_devs = 0;
$dev_stat_q = mysqli_query($conn, "SELECT 
  COUNT(*) as total,
  SUM(CASE WHEN device_mode=1 THEN 1 ELSE 0 END) as att,
  SUM(CASE WHEN device_mode=0 THEN 1 ELSE 0 END) as enr
  FROM devices");
if ($dev_stat_q && $drow = mysqli_fetch_assoc($dev_stat_q)) {
  $total_devs = (int)$drow['total'];
  $att_devs   = (int)$drow['att'];
  $enr_devs   = (int)$drow['enr'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Devices — RFID Attendance</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="css/devices.css"/>

<script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.js"
        integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
        crossorigin="anonymous">
</script>
<script type="text/javascript" src="js/bootbox.min.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script src="js/dev_config.js"></script>
<script>
function reloadDevicesList() {
    $.ajax({
        url: "dev_up.php",
        type: 'POST',
        data: { 'dev_up': 1 }
    }).done(function(data) {
        $('#devices').html(data);
        var q = $('#devSearchInput').val();
        if (q) filterTableRows(q, '#devicesTable');
    });
}
$(document).ready(function(){
    reloadDevicesList();
});
</script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1>ESP32 <span class="accent">Reader Devices</span></h1>
      <p class="page-subtitle">Manage hardware terminals, security tokens, and Enrollment / Attendance operating modes</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
      <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#new-device">
        <i class="fa fa-plus"></i> Add New Device
      </button>
    </div>
  </div>

  <!-- shadcn KPI Stat Cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Total ESP32 Readers</span>
        <span class="stat-value"><?php echo $total_devs; ?></span>
        <span class="stat-sub">Configured hardware units</span>
      </div>
      <div class="stat-icon indigo"><i class="fa fa-microchip"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Attendance Mode</span>
        <span class="stat-value"><?php echo $att_devs; ?></span>
        <span class="stat-sub">Logging Check-In &amp; Check-Out</span>
      </div>
      <div class="stat-icon emerald"><i class="fa fa-check-circle-o"></i></div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <span class="stat-label">Enrollment Mode</span>
        <span class="stat-value"><?php echo $enr_devs; ?></span>
        <span class="stat-sub">Scanning new RFID cards</span>
      </div>
      <div class="stat-icon amber"><i class="fa fa-id-card-o"></i></div>
    </div>
  </div>

  <div class="alert_dev"></div>

  <!-- Devices Table Card -->
  <div class="table-card">
    <div class="toolbar-wrap">
      <div class="search-box-wrap">
        <i class="fa fa-search search-icon"></i>
        <input type="text" id="devSearchInput" class="search-input" placeholder="Search devices by name, department, or token..." oninput="handleDevSearch(this.value)">
        <button type="button" id="devSearchClear" class="search-clear-btn" onclick="clearDevSearch()">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="actions-wrap">
        <button type="button" class="action-btn-sm" onclick="reloadDevicesList(); showToast('Refreshed', 'Device list reloaded.', 'info', 2000);">
          <i class="fa fa-refresh"></i> Refresh
        </button>
      </div>
    </div>
    <div id="devices"></div>
  </div>

  <!-- Add New Device Modal -->
  <div class="modal fade" id="new-device" tabindex="-1" role="dialog" aria-labelledby="New Device" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
      <div class="modal-content animate">
        <div class="modal-header">
          <h3 class="modal-title" id="exampleModalLongTitle">
            <span class="modal-title-icon"><i class="fa fa-microchip"></i></span>
            Register New ESP32 Device
          </h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <form action="" method="POST" enctype="multipart/form-data" onsubmit="return false;">
          <div class="modal-body">
            <label for="dev_name">Device Name</label>
            <input type="text" name="dev_name" id="dev_name" placeholder="e.g. Main Gate Reader" required/>
            <label for="dev_dep">Assigned Department / Location</label>
            <input type="text" name="dev_dep" id="dev_dep" placeholder="e.g. CSE Department" required style="margin-bottom:4px;"/>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" name="dev_add" id="dev_add" class="btn btn-primary">
              <i class="fa fa-plus"></i> Create Device
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ESP32 Firmware Snippet Modal -->
  <div class="modal fade" id="espConfigModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;" role="document">
      <div class="modal-content animate">
        <div class="modal-header">
          <h3 class="modal-title">
            <span class="modal-title-icon"><i class="fa fa-code"></i></span>
            ESP32 Arduino Configuration
          </h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p style="font-size:13px;color:var(--text-muted);margin-bottom:10px;">
            Copy and paste these lines into your ESP32 Arduino sketch for <b id="espModalDevName" style="color:var(--text-foreground);">Device</b>:
          </p>
          <pre class="code-snippet-box" id="espSnippetCode"></pre>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="copyEspSnippet()">
            <i class="fa fa-clone"></i> Copy Arduino Snippet
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
</main>
<script>
  function handleDevSearch(val) {
    var btn = document.getElementById('devSearchClear');
    if (btn) btn.style.display = val.length > 0 ? 'block' : 'none';
    filterTableRows(val, '#devicesTable');
  }
  function clearDevSearch() {
    var inp = document.getElementById('devSearchInput');
    if (inp) {
      inp.value = '';
      handleDevSearch('');
      inp.focus();
    }
  }
  function showEspSnippet(name, token) {
    document.getElementById('espModalDevName').textContent = name;
    var host = window.location.host || '10.78.74.243';
    var snippet = '// XAMPP server URL\n' +
                  'String serverURL = "http://' + host + '/Attendance%20Management/getdata.php";\n\n' +
                  '// Device Token (' + name + ')\n' +
                  'const char* device_token = "' + token + '";';
    document.getElementById('espSnippetCode').textContent = snippet;
    $('#espConfigModal').modal('show');
  }
  function copyEspSnippet() {
    var code = document.getElementById('espSnippetCode').textContent;
    copyUID(code);
  }
</script>
</body>
</html>
