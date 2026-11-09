<head>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.css"/>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/header.css"/>
	<script>
		// Apply saved theme immediately to prevent flash
		(function() {
			var savedTheme = localStorage.getItem('rfid_theme') || 'light';
			document.documentElement.setAttribute('data-theme', savedTheme);
		})();
	</script>
</head>
<header class="shadcn-navbar">
	<div class="navbar-container">
		<a href="index.php" class="brand-logo">
			<span class="brand-icon"><i class="fa fa-id-card-o"></i></span>
			<span class="brand-stroke-text">RFID ATTENDANCE</span>
			<span class="brand-tag">ESP32</span>
		</a>

		<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
		<nav class="topnav" id="myTopnav">
			<a href="index.php" <?php echo ($current_page == 'index.php') ? 'class="active-link"' : ''; ?>>
				<i class="fa fa-users"></i> Users
			</a>
			<a href="ManageUsers.php" <?php echo ($current_page == 'ManageUsers.php') ? 'class="active-link"' : ''; ?>>
				<i class="fa fa-user-plus"></i> Manage Users
			</a>
			<a href="UsersLog.php" <?php echo ($current_page == 'UsersLog.php') ? 'class="active-link"' : ''; ?>>
				<i class="fa fa-calendar-check-o"></i> Attendance Logs
			</a>
			<a href="devices.php" <?php echo ($current_page == 'devices.php') ? 'class="active-link"' : ''; ?>>
				<i class="fa fa-microchip"></i> Devices
			</a>
			<a href="telegram_settings.php" <?php echo ($current_page == 'telegram_settings.php') ? 'class="active-link"' : ''; ?>>
				<i class="fa fa-paper-plane-o"></i> Notifications
			</a>
		</nav>

		<div class="nav-right-actions">
			<button type="button" class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Switch Light / Dark Theme">
				<i class="fa fa-moon-o" id="themeIcon"></i>
			</button>
			<?php
				if (isset($_SESSION['Admin-name'])) {
					echo '<a href="#" class="nav-profile-btn" data-toggle="modal" data-target="#admin-account" title="Account Settings">
							<i class="fa fa-user-circle-o" style="color:var(--primary);"></i> <span>'.htmlspecialchars($_SESSION['Admin-name']).'</span>
						  </a>';
					echo '<a href="logout.php" class="nav-logout-btn" title="Sign Out">
							<i class="fa fa-sign-out"></i> <span>Log Out</span>
						  </a>';
				} else {
					echo '<a href="login.php" class="nav-profile-btn"><i class="fa fa-sign-in"></i> Log In</a>';
				}
			?>
			<a href="javascript:void(0);" class="theme-toggle-btn" style="display:none;" id="mobileMenuBtn" onclick="navFunction()">
				<i class="fa fa-bars"></i>
			</a>
		</div>
	</div>
	<div class="up_info1"></div>
	<div class="up_info2"></div>
</header>

<!-- Toast Notification Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Universal shadcn Confirmation AlertDialog -->
<div class="modal fade" id="softConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;" role="document">
    <div class="modal-content animate">
      <div class="modal-body" style="padding: 24px !important;">
        <div class="confirm-dialog-content">
          <div class="confirm-header-row">
            <div id="confirmIconBadge" class="confirm-icon-badge danger">
              <i id="confirmIconEl" class="fa fa-exclamation-triangle"></i>
            </div>
            <div class="confirm-text-wrap">
              <h4 id="confirmTitleEl" class="confirm-title">Are you sure?</h4>
              <p id="confirmDescEl" class="confirm-description">This action cannot be undone.</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="confirmCancelBtn">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmProceedBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Admin Account Settings Modal -->
<div class="modal fade" id="admin-account" tabindex="-1" role="dialog" aria-labelledby="Admin Update" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;" role="document">
    <div class="modal-content animate">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLongTitle">
          <span class="modal-title-icon"><i class="fa fa-sliders"></i></span>
          Account Settings
        </h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="ac_update.php" method="POST" enctype="multipart/form-data">
	      <div class="modal-body">
	      	<label for="up_name">Administrator Name</label>
	      	<input type="text" name="up_name" id="up_name" placeholder="Enter your name..." value="<?php echo isset($_SESSION['Admin-name']) ? htmlspecialchars($_SESSION['Admin-name']) : ''; ?>" required/>
	      	<label for="up_email">Email Address</label>
	      	<input type="email" name="up_email" id="up_email" placeholder="Enter your email..." value="<?php echo isset($_SESSION['Admin-email']) ? htmlspecialchars($_SESSION['Admin-email']) : ''; ?>" required/>
	      	<label for="up_pwd">Current Password <span style="color:var(--text-subtle);font-weight:400;">(Required to save)</span></label>
	      	<input type="password" name="up_pwd" id="up_pwd" placeholder="Enter your current password..." required style="margin-bottom:4px;"/>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	        <button type="submit" name="update" class="btn btn-primary"><i class="fa fa-check"></i> Save Changes</button>
	      </div>
	  </form>
    </div>
  </div>
</div>

<script>
	function navFunction() {
		var x = document.getElementById("myTopnav");
		if (x.className === "topnav") {
			x.className += " responsive";
		} else {
			x.className = "topnav";
		}
	}

	function updateThemeIcon() {
		var current = document.documentElement.getAttribute('data-theme') || 'light';
		var icon = document.getElementById('themeIcon');
		if (icon) {
			icon.className = (current === 'dark') ? 'fa fa-sun-o' : 'fa fa-moon-o';
		}
	}

	function toggleTheme() {
		var current = document.documentElement.getAttribute('data-theme') || 'light';
		var next = (current === 'dark') ? 'light' : 'dark';
		document.documentElement.setAttribute('data-theme', next);
		localStorage.setItem('rfid_theme', next);
		updateThemeIcon();
		showToast('Theme Updated', 'Switched to ' + (next === 'dark' ? 'Dark' : 'Soft Light') + ' mode.', 'info', 2200);
	}

	// Responsive hamburger visibility
	function checkMobileNav() {
		var btn = document.getElementById('mobileMenuBtn');
		if (btn) {
			btn.style.display = (window.innerWidth <= 900) ? 'inline-flex' : 'none';
		}
	}
	window.addEventListener('resize', checkMobileNav);
	document.addEventListener('DOMContentLoaded', function() {
		updateThemeIcon();
		checkMobileNav();
	});

	// Sonner-style Soft Toast Notification
	function showToast(title, message, type, duration) {
		type = type || 'info';
		duration = duration || 3800;
		var container = document.getElementById('toast-container');
		if (!container) return;

		var iconMap = {
			success: 'fa-check',
			danger: 'fa-exclamation',
			warning: 'fa-bell-o',
			info: 'fa-info'
		};

		var toast = document.createElement('div');
		toast.className = 'soft-toast toast-' + type;
		toast.innerHTML =
			'<div class="toast-icon"><i class="fa ' + (iconMap[type] || 'fa-info') + '"></i></div>' +
			'<div class="toast-content">' +
				'<div class="toast-title">' + title + '</div>' +
				(message ? '<div class="toast-msg">' + message + '</div>' : '') +
			'</div>' +
			'<button type="button" class="toast-close" aria-label="Close">&times;</button>';

		container.appendChild(toast);
		toast.querySelector('.toast-close').onclick = function() {
			toast.classList.remove('show');
			setTimeout(function() { toast.remove(); }, 250);
		};

		requestAnimationFrame(function() {
			toast.classList.add('show');
		});

		setTimeout(function() {
			if (toast && toast.parentNode) {
				toast.classList.remove('show');
				setTimeout(function() { if (toast.parentNode) toast.remove(); }, 250);
			}
		}, duration);
	}

	// Universal shadcn Confirmation AlertDialog
	var _confirmCallback = null;
	var _cancelCallback = null;
	function showConfirmModal(options) {
		options = options || {};
		var title = options.title || 'Confirm Action';
		var message = options.message || 'Are you sure you want to proceed with this action?';
		var variant = options.variant || 'danger'; // 'danger', 'warning', 'primary'
		var confirmText = options.confirmText || 'Confirm';
		var iconClass = options.icon || (variant === 'danger' ? 'fa-trash-o' : (variant === 'warning' ? 'fa-refresh' : 'fa-question-circle'));

		document.getElementById('confirmTitleEl').textContent = title;
		document.getElementById('confirmDescEl').innerHTML = message;

		var badge = document.getElementById('confirmIconBadge');
		badge.className = 'confirm-icon-badge ' + variant;
		document.getElementById('confirmIconEl').className = 'fa ' + iconClass;

		var proceedBtn = document.getElementById('confirmProceedBtn');
		proceedBtn.textContent = confirmText;
		proceedBtn.className = 'btn ' + (variant === 'danger' ? 'btn-danger' : (variant === 'warning' ? 'btn-warning' : 'btn-primary'));

		_confirmCallback = options.onConfirm || null;
		_cancelCallback = options.onCancel || null;

		$('#softConfirmModal').modal('show');
	}

	$(document).on('click', '#confirmProceedBtn', function() {
		var cb = _confirmCallback;
		_confirmCallback = null;
		_cancelCallback = null;
		$('#softConfirmModal').modal('hide');
		if (typeof cb === 'function') cb();
	});

	$(document).on('hidden.bs.modal', '#softConfirmModal', function() {
		if (typeof _cancelCallback === 'function') {
			var cb = _cancelCallback;
			_cancelCallback = null;
			cb();
		}
	});

	// Click-to-copy UID helper
	function copyUID(uid, event) {
		if (event) event.stopPropagation();
		if (!uid) return;
		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(uid).then(function() {
				showToast('Card UID Copied', 'UID ' + uid + ' copied to clipboard.', 'success', 2500);
			});
		} else {
			var temp = document.createElement('input');
			document.body.appendChild(temp);
			temp.value = uid;
			temp.select();
			document.execCommand('copy');
			document.body.removeChild(temp);
			showToast('Card UID Copied', 'UID ' + uid + ' copied to clipboard.', 'success', 2500);
		}
	}

	// Instant Client-Side Live Search Helper
	function filterTableRows(searchVal, tableSelector) {
		var query = (searchVal || '').toLowerCase().trim();
		var rows = document.querySelectorAll(tableSelector + ' tbody tr');
		rows.forEach(function(row) {
			if (row.querySelector('.empty-state') || row.cells.length <= 1) return;
			var text = row.textContent.toLowerCase();
			row.style.display = (text.indexOf(query) > -1) ? '' : 'none';
		});
	}
</script>
<?php
	if (isset($_GET['error']) && $_GET['error'] == "wrongpasswordup") {
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				showToast("Incorrect Password", "Please enter your current password to update account settings.", "danger");
				$("#admin-account").modal("show");
			});
		</script>';
	}
	if (isset($_GET['success']) && $_GET['success'] == "updated") {
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				showToast("Account Updated", "Your administrator profile has been saved.", "success");
			});
		</script>';
	}
	if (isset($_GET['login']) && $_GET['login'] == "success") {
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				showToast("Welcome Back", "You have successfully signed in.", "success");
			});
		</script>';
	}
?>
