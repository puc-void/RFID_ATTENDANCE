<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Manage Users — RFID Attendance</title>
  	<meta charset="utf-8">
  	<meta name="viewport" content="width=device-width, initial-scale=1">
  	<link rel="icon" type="image/png" href="images/favicon.png">
	<link rel="stylesheet" type="text/css" href="css/manageusers.css">

    <script type="text/javascript" src="js/jquery-2.2.3.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.3.1.js"
	        integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
	        crossorigin="anonymous">
	</script>
    <script type="text/javascript" src="js/bootbox.min.js"></script>
	<script type="text/javascript" src="js/bootstrap.js"></script>
	<script src="js/manage_users.js"></script>
	<script>
	  function refreshManageUsersTable() {
	    $.ajax({
	      url: "manage_users_up.php"
	    }).done(function(data) {
	      $('#manage_users').html(data);
	      var q = $('#manageSearchInput').val();
	      if (q) filterTableRows(q, '#manageUsersTable');
	    });
	  }

	  $(document).ready(function(){
	    refreshManageUsersTable();
	    setInterval(function(){
	      refreshManageUsersTable();
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
				<h1>User <span class="accent">Management</span></h1>
				<p class="page-subtitle">Scan a card in Enrollment mode, click its UID badge in the table, and assign user credentials</p>
			</div>
			<div style="display:flex;align-items:center;gap:10px;">
				<div class="stat-badge">
					<span class="dot"></span>
					Live Sync (5s)
				</div>
				<button type="button" class="action-btn-sm" onclick="refreshManageUsersTable(); showToast('Refreshed', 'User table synced with database.', 'info', 2000);">
					<i class="fa fa-refresh"></i> Sync Now
				</button>
			</div>
		</div>

		<div class="manage-grid">
			<!-- Left: shadcn Form Card -->
			<div class="form-style-5">
				<div class="form-card-header">
					<div class="form-card-title">
						<i class="fa fa-id-card" style="color:var(--primary);"></i>
						Card Assignment
					</div>
					<span id="selectedCardBadge" class="selected-indicator">No Card Selected</span>
				</div>

				<form enctype="multipart/form-data" id="manageUserForm" onsubmit="return false;">
					<div class="alert_user"></div>
					<fieldset>
						<legend><span class="number">1</span> User Credentials</legend>
						<input type="hidden" name="user_id" id="user_id">

						<label for="name">Full Name</label>
						<input type="text" name="name" id="name" placeholder="e.g. John Doe">

						<label for="number">Student / Serial ID</label>
						<input type="text" name="number" id="number" placeholder="e.g. 2024105">

						<label for="phone">Phone Number</label>
						<input type="text" name="phone" id="phone" placeholder="e.g. 01700000000">

						<label for="email">Email Address</label>
						<input type="email" name="email" id="email" placeholder="e.g. john@university.edu">
					</fieldset>

					<fieldset style="margin-bottom: 6px;">
						<legend><span class="number">2</span> Department &amp; Gender</legend>

						<label for="dev_sel">Assigned Department</label>
						<select class="dev_sel" name="dev_sel" id="dev_sel">
							<option value="0">All Departments</option>
							<?php
								require 'connectDB.php';
								$sql = "SELECT * FROM devices ORDER BY device_name ASC";
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

						<label>Gender</label>
						<div class="gender-group">
							<label class="gender-option">
								<input type="radio" name="gender" class="gender" value="Male" checked="checked">
								<span><i class="fa fa-mars"></i> Male</span>
							</label>
							<label class="gender-option">
								<input type="radio" name="gender" class="gender" value="Female">
								<span><i class="fa fa-venus"></i> Female</span>
							</label>
						</div>
					</fieldset>

					<div class="form-actions-stack">
						<button type="button" name="user_add" class="user_add">
							<i class="fa fa-user-plus"></i> Register Selected Card
						</button>
						<div class="form-actions-row">
							<button type="button" name="user_upd" class="user_upd">
								<i class="fa fa-check"></i> Update Info
							</button>
							<button type="button" name="user_rmo" class="user_rmo">
								<i class="fa fa-trash-o"></i> Remove User
							</button>
						</div>
						<button type="button" class="action-btn-sm" style="justify-content:center;width:100%;" onclick="clearUserForm()">
							<i class="fa fa-eraser"></i> Clear Form
						</button>
					</div>
				</form>
			</div>

			<!-- Right: Scanned & Registered Cards Table -->
			<div class="table-card">
				<div class="toolbar-wrap">
					<div class="search-box-wrap">
						<i class="fa fa-search search-icon"></i>
						<input type="text" id="manageSearchInput" class="search-input" placeholder="Filter cards by UID, name, serial, department..." oninput="handleManageSearch(this.value)">
						<button type="button" id="manageSearchClear" class="search-clear-btn" onclick="clearManageSearch()">
							<i class="fa fa-times"></i>
						</button>
					</div>
					<div class="actions-wrap">
						<span style="font-size:12px;color:var(--text-muted);">
							<i class="fa fa-hand-pointer-o"></i> Click a <b>Card UID</b> to load into form
						</span>
					</div>
				</div>
				<div id="manage_users"></div>
			</div>
		</div>
	</div>
</main>
<script>
	function handleManageSearch(val) {
		var btn = document.getElementById('manageSearchClear');
		if (btn) btn.style.display = val.length > 0 ? 'block' : 'none';
		filterTableRows(val, '#manageUsersTable');
	}
	function clearManageSearch() {
		var inp = document.getElementById('manageSearchInput');
		if (inp) {
			inp.value = '';
			handleManageSearch('');
			inp.focus();
		}
	}
	function clearUserForm() {
		$('#user_id').val('');
		$('#name').val('');
		$('#number').val('');
		$('#phone').val('');
		$('#email').val('');
		$('#dev_sel').val('0');
		$('#selectedCardBadge').removeClass('active').text('No Card Selected');
		showToast('Form Cleared', 'Ready for a new card selection.', 'info', 2000);
	}
</script>
</body>
</html>
