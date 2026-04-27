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
$sql = "SELECT * FROM users_logs WHERE ".$_SESSION['searchQuery']." ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

?>

<div class="table-responsive" style="max-height: 500px;">
<table class="table">
<thead class="table-primary">
<tr>
<th>ID</th>
<th>Name</th>
<th>Serial Number</th>
<th>Card UID</th>
<th>Device Dep</th>
<th>Date</th>
<th>Time In</th>
<th>Time Out</th>
</tr>
</thead>
<tbody class="table-secondary">
<?php
if ($result && mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    ?>
    <tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['username'] ?></td>
    <td><span style="font-weight: 500;"><?php echo ($row['serialnumber'] != 0) ? $row['serialnumber'] : '---'; ?></span></td>
    <td><?= $row['card_uid'] ?></td>
    <td><?= $row['device_dep'] ?></td>
    <td><?= $row['checkindate'] ?></td>
    <td><?= $row['timein'] ?></td>
    <td><?= $row['timeout'] ?></td>
    </tr>
    <?php
  }
}
?>
</tbody>
</table>
</div>
