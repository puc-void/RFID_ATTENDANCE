<?php
session_start();
if (!isset($_SESSION['Admin-name'])) {
  header("location: login.php");
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
	  	$(window).on("load resize ", function() {
		    var scrollWidth = $('.tbl-content').width() - $('.tbl-content table').width();
		    $('.tbl-header').css({'padding-right':scrollWidth});
		}).resize();
	</script>
	<script>
	  $(document).ready(function(){
	  	  $.ajax({
	        url: "manage_users_up.php"
	        }).done(function(data) {
	        $('#manage_users').html(data);
	      });
	    setInterval(function(){
	      $.ajax({
	        url: "manage_users_up.php"
	        }).done(function(data) {
	        $('#manage_users').html(data);
	      });
	    },5000);
	  });
	</script>
</head>
<body>
<?php include 'header.php'; ?>
<main>
	<div class="page-wrapper" style="width: 100%; display: flex; flex-direction: column; gap: 0;">
		<h1 class="slideInDown animated">User <span style="color:#00F0FF;">Management</span></h1>
		
		<div style="display: flex; flex-wrap: wrap; gap: 28px; width: 100%; align-items: flex-start;">
			<!-- Form Section -->
			<div class="form-style-5 slideInDown animated">
				<form enctype="multipart/form-data">
					<div class="alert_user"></div>
					<fieldset>
						<legend><span class="number">1</span> User Credentials</legend>
						<input type="hidden" name="user_id" id="user_id">
						
						<label for="name">Full Name</label>
						<input type="text" name="name" id="name" placeholder="E.g. John Doe">
						
						<label for="number">Serial Number</label>
						<input type="text" name="number" id="number" placeholder="E.g. 123456">
						
						<label for="email">Email Address</label>
						<input type="email" name="email" id="email" placeholder="E.g. john@example.com">
					</fieldset>
					
					<fieldset>
						<legend><span class="number">2</span> Assignment</legend>
						
						<label for="dev_sel">Assigned Department</label>
						<select class="dev_sel" name="dev_sel" id="dev_sel">
							<option value="0">Default (All Departments)</option>
							<?php
								require 'connectDB.php';
								$sql = "SELECT * FROM devices ORDER BY device_name ASC";
								$result = mysqli_stmt_init($conn);
								if (mysqli_stmt_prepare($result, $sql)) {
									mysqli_stmt_execute($result);
									$resultl = mysqli_stmt_get_result($result);
									while ($row = mysqli_fetch_assoc($resultl)) {
										echo '<option value="'.$row['device_uid'].'">'.$row['device_dep'].'</option>';
									}
								}
							?>
						</select>

						<label>Gender</label>
						<div class="gender-group">
							<label class="gender-option">
								<input type="radio" name="gender" class="gender" value="Male" checked="checked">
								<span>Male</span>
							</label>
							<label class="gender-option">
								<input type="radio" name="gender" class="gender" value="Female">
								<span>Female</span>
							</label>
						</div>
					</fieldset>

					<div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
						<button type="button" name="user_add" class="user_add">+ Register User</button>
						<button type="button" name="user_upd" class="user_upd">✎ Update Information</button>
						<button type="button" name="user_rmo" class="user_rmo">✕ Terminate User</button>
					</div>
				</form>
			</div>

			<!-- Table Section -->
			<div class="section" style="flex: 1; min-width: 400px;">
				<div class="slideInRight animated">
					<div id="manage_users"></div>
				</div>
			</div>
		</div>
	</div>
</main>
</body>
</html>