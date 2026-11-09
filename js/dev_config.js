$(document).ready(function(){

	// Add New Device
	$(document).on('click', '#dev_add', function(){
		var dev_name = $('#dev_name').val();
		var dev_dep = $('#dev_dep').val();

		if (!dev_name || !dev_dep) {
			if (typeof showToast === 'function') {
				showToast('Missing Fields', 'Please enter both a Device Name and Department.', 'warning');
			}
			return;
		}

		$.ajax({
		  url: 'dev_config.php',
		  type: 'POST',
		  data: {
		    'dev_add': 1,
		    'dev_name': dev_name,
		    'dev_dep': dev_dep,
		  },
		  success: function(response){
		    $('#dev_name').val('');
		    $('#dev_dep').val('');

		    if (response == 1) {
		        $('#new-device').modal('hide');
		        if (typeof showToast === 'function') {
		        	showToast('Device Created', 'New ESP32 reader device has been added.', 'success');
		        }
		    } else {
		        if (typeof showToast === 'function') {
		        	showToast('Device Error', $(response).text() || response, 'danger');
		        }
		    }

		    $.ajax({
		      url: "dev_up.php",
		      type: 'POST',
		      data: { 'dev_up': 1 }
		    }).done(function(data) {
		      $('#devices').html(data);
		    });
		  }
		});
	});

	// Regenerate Device Token with shadcn Confirmation Modal
	$(document).on('click', '.dev_uid_up', function(){
		var dev_id = $(this).data('id');

		var performTokenUpdate = function() {
			$.ajax({
				url: 'dev_config.php',
				type: 'POST',
				data: {
					'dev_uid_up': 1,
					'dev_id_up': dev_id,
				},
				success: function(response){
					if (response == 1) {
						if (typeof showToast === 'function') {
							showToast('Token Regenerated', 'Copy the new token and update your ESP32 Arduino sketch.', 'success');
						}
						$.ajax({
							url: "dev_up.php",
							type: 'POST',
							data: { 'dev_up': 1 }
						}).done(function(data) {
							$('#devices').html(data);
						});
					} else {
						if (typeof showToast === 'function') {
							showToast('Update Failed', response, 'danger');
						}
					}
				}
			});
		};

		if (typeof showConfirmModal === 'function') {
			showConfirmModal({
				title: 'Regenerate Device Token?',
				message: 'Generating a new security token will invalidate the old token. Make sure to update <code>device_token</code> in your ESP32 Arduino code afterwards.',
				variant: 'warning',
				confirmText: 'Regenerate Token',
				onConfirm: performTokenUpdate
			});
		} else {
			bootbox.confirm("Do you really want to Update this Device Token?", function(result) {
				if (result) performTokenUpdate();
			});
		}
	});

	// Delete Device with shadcn Confirmation Modal
	$(document).on('click', '.dev_del', function(){
		var deleteid = $(this).data('id');

		var performDeleteDevice = function() {
			$.ajax({
				url: 'dev_config.php',
				type: 'POST',
				data: {
					'dev_del': 1,
					'dev_sel': deleteid,
				},
				success: function(response){
					if (response == 1) {
						if (typeof showToast === 'function') {
							showToast('Device Deleted', 'The reader device has been removed.', 'success');
						}
						$.ajax({
							url: "dev_up.php",
							type: 'POST',
							data: { 'dev_up': 1 }
						}).done(function(data) {
							$('#devices').html(data);
						});
					} else {
						if (typeof showToast === 'function') {
							showToast('Delete Failed', response, 'danger');
						}
					}
				}
			});
		};

		if (typeof showConfirmModal === 'function') {
			showConfirmModal({
				title: 'Delete ESP32 Reader?',
				message: 'Are you sure you want to permanently remove this device? Scans from its token will no longer be accepted.',
				variant: 'danger',
				confirmText: 'Yes, Delete Device',
				onConfirm: performDeleteDevice
			});
		} else {
			bootbox.confirm("Do you really want to delete this Device?", function(result) {
				if (result) performDeleteDevice();
			});
		}
	});

	// Switch Device Operating Mode (Enrollment vs Attendance)
	$(document).on('click', '.mode_sel', function(){
    	var dev_mode = $(this).attr("value");
		var dev_id = $(this).data('id');
		var modeLabel = (dev_mode == "1") ? "Attendance (Check-In / Out)" : "Enrollment (Register New Cards)";

		var reloadDevices = function() {
			$.ajax({
				url: "dev_up.php",
				type: 'POST',
				data: { 'dev_up': 1 }
			}).done(function(data) {
				$('#devices').html(data);
			});
		};

		var performModeSwitch = function() {
			$.ajax({
				url: 'dev_config.php',
				type: 'POST',
				data: {
					'dev_mode_set': 1,
					'dev_mode': dev_mode,
					'dev_id': dev_id,
				},
				success: function(response){
					if (response == 1) {
						if (typeof showToast === 'function') {
							showToast('Mode Updated', 'Device switched to ' + modeLabel + '.', 'success');
						}
					} else {
						if (typeof showToast === 'function') {
							showToast('Mode Update Error', response, 'danger');
						}
					}
					reloadDevices();
				}
			});
		};

		if (typeof showConfirmModal === 'function') {
			showConfirmModal({
				title: 'Switch Operating Mode?',
				message: 'Change this ESP32 reader to <b>' + modeLabel + '</b> mode?',
				variant: 'primary',
				icon: 'fa-exchange',
				confirmText: 'Switch Mode',
				onConfirm: performModeSwitch,
				onCancel: reloadDevices
			});
		} else {
			bootbox.confirm("Do you really want to change this Device Mode?", function(result) {
				if (result) performModeSwitch();
				else reloadDevices();
			});
		}
	});
});
