<?php
session_start();
require 'connectDB.php';

// Enable debug (you may disable later)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ----------------------------------------------------------
// DEFAULT SEARCH QUERY (VERY IMPORTANT)
// ----------------------------------------------------------
if (!isset($_SESSION['searchQuery']) || empty($_SESSION['searchQuery'])) {
  $_SESSION['searchQuery'] = "1";  // Always true
}

// ----------------------------------------------------------
// FIRST PRIORITY — select_date = 1 (Page load default)
// ----------------------------------------------------------
if (isset($_POST['select_date']) && $_POST['select_date'] == 1) {
  $today = date("Y-m-d");
  $_SESSION['searchQuery'] = "checkindate='".$today."'";
}

// ----------------------------------------------------------
// LOG FILTER FROM MODAL
// ----------------------------------------------------------
if (isset($_POST['log_date'])) {

  // ------------------ DATE RANGE -------------------------
  if (!empty($_POST['date_sel_start'])) {
    $Start_date = $_POST['date_sel_start'];
    $_SESSION['searchQuery'] = "checkindate='".$Start_date."'";
  }

  if (!empty($_POST['date_sel_end'])) {
    $End_date = $_POST['date_sel_end'];
    $_SESSION['searchQuery'] = "checkindate BETWEEN '".$Start_date."' AND '".$End_date."'";
  }

  // ------------------ TIME-IN FILTER ---------------------
  if ($_POST['time_sel'] == "Time_in") {

    if (!empty($_POST['time_sel_start']) && empty($_POST['time_sel_end'])) {
      $_SESSION['searchQuery'] .= " AND timein='".$_POST['time_sel_start']."'";
    }

    if (!empty($_POST['time_sel_start']) && !empty($_POST['time_sel_end'])) {
      $_SESSION['searchQuery'] .=
      " AND timein BETWEEN '".$_POST['time_sel_start']."' AND '".$_POST['time_sel_end']."'";
    }
  }

  // ------------------ TIME-OUT FILTER ---------------------
  if ($_POST['time_sel'] == "Time_out") {

    if (!empty($_POST['time_sel_start']) && empty($_POST['time_sel_end'])) {
      $_SESSION['searchQuery'] .= " AND timeout='".$_POST['time_sel_start']."'";
    }

    if (!empty($_POST['time_sel_start']) && !empty($_POST['time_sel_end'])) {
      $_SESSION['searchQuery'] .=
      " AND timeout BETWEEN '".$_POST['time_sel_start']."' AND '".$_POST['time_sel_end']."'";
    }
  }

  // ------------------ USER FILTER ---------------------
  if ($_POST['card_sel'] != 0) {
    $_SESSION['searchQuery'] .= " AND card_uid='".$_POST['card_sel']."'";
  }

  // ------------------ DEVICE FILTER (FIXED) ---------------------
  if ($_POST['dev_sel'] != 0) {
    $_SESSION['searchQuery'] .= " AND device_uid='".$_POST['dev_sel']."'";
  }
}

// ----------------------------------------------------------
// FINAL QUERY
// ----------------------------------------------------------
$sql = "SELECT *, (SELECT gender FROM users WHERE card_uid = users_logs.card_uid LIMIT 1) as gender FROM users_logs WHERE ".$_SESSION['searchQuery']." ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

?>

<div class="table-responsive" style="max-height: 800px;"> 
  <table class="table">
    <thead>
      <tr>
        <th>ID | Card UID</th>
        <th>User Details</th>
        <th>S.No</th>
        <th>Date</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Department</th>
      </tr>
    </thead>
    <tbody>
    <?php
      if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $initial = strtoupper(substr($row['username'] ?? 'U', 0, 1));
            $gender = $row['gender'] ?? 'None';
            $gender_class = strtolower($gender) === 'female' ? 'female' : 'male';
      ?>
            <tr>
              <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                  <span style="color: #4a5568; font-size: 11px; font-weight: 600; min-width: 20px;"><?php echo $row['id'];?></span>
                  <div style="height: 12px; width: 1px; background: rgba(255,255,255,0.1);"></div>
                  <button type="button" class="select_btn" 
                          style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #00F0FF; 
                                 font-family: 'Courier New', monospace; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                    <?php echo $row['card_uid'];?>
                  </button>
                </div>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, rgba(0,240,255,0.1), rgba(189,0,255,0.1)); 
                              border: 1px solid rgba(0,240,255,0.2); display: flex; align-items: center; justify-content: center; 
                              font-size: 12px; font-weight: 700; color: #00F0FF; flex-shrink: 0;">
                    <?php echo $initial; ?>
                  </div>
                  <div>
                    <div style="font-weight: 500; color: #f0f4ff;"><?php echo htmlspecialchars($row['username']);?></div>
                    <div style="font-size: 11px; color: #8892aa;">
                      <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: <?php echo ($gender_class == 'male' ? '#60a5fa' : '#f472b6');?>; margin-right: 4px;"></span>
                      <?php echo $gender;?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <span style="color: #f0f4ff; font-size: 14px; font-weight: 500;">
                  <?php echo ($row['serialnumber'] != 0) ? $row['serialnumber'] : '---'; ?>
                </span>
              </td>
              <td><span style="color: #8892aa; font-size: 13px;"><?php echo $row['checkindate'];?></span></td>
              <td>
                <div class="time-badge time-in">
                  <i class="fa fa-sign-in"></i> <?php echo $row['timein'];?>
                </div>
              </td>
              <td>
                <div class="time-badge time-out">
                  <i class="fa fa-sign-out"></i> <?php echo $row['timeout'];?>
                </div>
              </td>
              <td>
                <span style="display: inline-block; padding: 3px 10px; background: rgba(189, 0, 255, 0.07); border: 1px solid rgba(189, 0, 255, 0.2); 
                             color: #d866ff; border-radius: 6px; font-size: 12px; font-weight: 500;">
                  <?php echo ($row['device_dep'] == "0") ? "All" : htmlspecialchars($row['device_dep']);?>
                </span>
              </td>
            </tr>
      <?php
        }   
      } else {
        echo '<tr><td colspan="7" style="text-align:center; padding:40px; color:#4a5568;">No logs found for this selection.</td></tr>';
      }
    ?>
    </tbody>
  </table>
</div>
