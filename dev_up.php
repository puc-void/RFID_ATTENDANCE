<?php 
session_start();
?>
<div class="table-responsive">
	<table class="table" id="devicesTable">
		<thead>
	      <tr>
	        <th>Device Name</th>
	        <th>Department</th>
	        <th>Device Token (UID)</th>
	        <th>Date Added</th>
	        <th>Operating Mode</th>
	        <th style="text-align:right !important;">Actions</th>
	      </tr>
    	</thead>
    	<tbody>
			<?php  
		    	require 'connectDB.php';
		    	$sql = "SELECT * FROM devices ORDER BY id DESC";
				$result = mysqli_stmt_init($conn);
				if (!mysqli_stmt_prepare($result, $sql)) {
				    echo '<tr><td colspan="6" style="text-align:center;padding:32px;color:var(--danger);">SQL Error</td></tr>';
				} else {
				    mysqli_stmt_execute($result);
				    $resultl = mysqli_stmt_get_result($result);
				    if (mysqli_num_rows($resultl) > 0) {
					    while ($row = mysqli_fetch_assoc($resultl)) {
					      	$radio1 = ($row["device_mode"] == 0) ? "checked" : "" ;
					      	$radio2 = ($row["device_mode"] == 1) ? "checked" : "" ;

					      	$de_mode = '<div class="mode_select">
					      	  <input type="radio" id="'.$row["id"].'-one" name="'.$row["id"].'" class="mode_sel" data-id="'.$row["id"].'" value="0" '.$radio1.'/>
					      	  <label for="'.$row["id"].'-one"><i class="fa fa-id-card-o"></i> Enrollment</label>
		                      <input type="radio" id="'.$row["id"].'-two" name="'.$row["id"].'" class="mode_sel" data-id="'.$row["id"].'" value="1" '.$radio2.'/>
					      	  <label for="'.$row["id"].'-two"><i class="fa fa-check-circle-o"></i> Attendance</label>
					      	</div>';

					    	echo '<tr>
							        <td>
							          <div style="display:flex;align-items:center;gap:10px;">
							            <div class="user-avatar-bubble"><i class="fa fa-microchip"></i></div>
							            <span style="font-weight:600;color:var(--text-foreground);">'.htmlspecialchars($row["device_name"]).'</span>
							          </div>
							        </td>
							        <td><span class="dept-badge"><i class="fa fa-building-o"></i> '.htmlspecialchars($row["device_dep"]).'</span></td>
							        <td>
							          <div style="display:inline-flex;align-items:center;gap:6px;">
							            <button type="button" class="btn-warning" style="height:28px;padding:0 8px !important;border-radius:6px;cursor:pointer;" class_old="dev_uid_up" onclick="" title="Regenerate Device Token">
							              <span class="dev_uid_up" data-id="'.$row["id"].'" style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;font-weight:600;">
							                <i class="fa fa-refresh"></i>
							              </span>
							            </button>
							            <button type="button" class="uid-code" onclick="copyUID(\''.htmlspecialchars($row["device_uid"], ENT_QUOTES).'\', event)" title="Click to copy Device Token">
							              '.htmlspecialchars($row["device_uid"]).'
							              <i class="fa fa-clone" style="font-size:11px;opacity:0.65;"></i>
							            </button>
							          </div>
							        </td>
							        <td style="color:var(--text-muted);font-size:13px;">'.htmlspecialchars($row["device_date"]).'</td>
							        <td>'.$de_mode.'</td>
							        <td style="text-align:right;">
							          <div style="display:inline-flex;align-items:center;gap:6px;">
							            <button type="button" class="action-btn-sm" onclick="showEspSnippet(\''.htmlspecialchars($row["device_name"], ENT_QUOTES).'\', \''.htmlspecialchars($row["device_uid"], ENT_QUOTES).'\')" title="View ESP32 Arduino Code">
							              <i class="fa fa-code"></i> ESP32 Code
							            </button>
							            <button type="button" class="dev_del btn btn-danger" style="height:32px;padding:0 10px !important;" data-id="'.$row["id"].'" title="Delete this device">
							              <i class="fa fa-trash-o"></i>
							            </button>
							          </div>
							        </td>
							      </tr>';
					    }
				    } else {
				        echo '<tr><td colspan="6" class="empty-state" style="text-align:center;padding:48px 20px;color:var(--text-muted);">No ESP32 reader devices configured yet. Click <b>Add New Device</b> above to create one.</td></tr>';
				    }
				}
		    ?>
    	</tbody>
	</table>
</div>
