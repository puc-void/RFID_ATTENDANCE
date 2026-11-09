<?php
/* Database connection settings */
	$servername = "localhost";
    $username = "root";		//put your phpmyadmin username.(default is "root")
    $password = "";			//if your phpmyadmin has a password put it here.(default is "root")
    $dbname = "rfidattendance";
    
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	
	if ($conn->connect_error) {
        die("Database Connection failed: " . $conn->connect_error);
    }

    // Ensure phone column exists in users table
    $phoneColCheck = @mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'phone'");
    if ($phoneColCheck && mysqli_num_rows($phoneColCheck) === 0) {
        @mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(30) NOT NULL DEFAULT 'None' AFTER `gender`");
    }

    // Ensure departments table exists
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `departments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `dept_code` VARCHAR(30) NOT NULL,
        `dept_name` VARCHAR(120) NOT NULL,
        `created_at` DATE NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `dept_code` (`dept_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure courses table exists
    @mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `courses` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `course_code` VARCHAR(30) NOT NULL,
        `course_title` VARCHAR(120) NOT NULL,
        `dept_name` VARCHAR(50) NOT NULL,
        `teacher_name` VARCHAR(80) NOT NULL DEFAULT '',
        `credit` DECIMAL(3,1) NOT NULL DEFAULT 3.0,
        `is_active_session` TINYINT(1) NOT NULL DEFAULT 0,
        `created_at` DATE NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `course_code` (`course_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Ensure course columns exist in users_logs table
    $courseColCheck = @mysqli_query($conn, "SHOW COLUMNS FROM `users_logs` LIKE 'course_id'");
    if ($courseColCheck && mysqli_num_rows($courseColCheck) === 0) {
        @mysqli_query($conn, "ALTER TABLE `users_logs`
            ADD COLUMN `course_id` INT(11) NOT NULL DEFAULT 0 AFTER `device_dep`,
            ADD COLUMN `course_code` VARCHAR(30) NOT NULL DEFAULT '' AFTER `course_id`,
            ADD COLUMN `course_title` VARCHAR(120) NOT NULL DEFAULT '' AFTER `course_code`");
    }

    // Sync any existing device/user departments into departments table automatically
    @mysqli_query($conn, "INSERT IGNORE INTO `departments` (`dept_code`, `dept_name`, `created_at`)
        SELECT DISTINCT `device_dep`, `device_dep`, CURDATE() FROM `devices`
        WHERE `device_dep` != '' AND `device_dep` != '0' AND `device_dep` != 'All'");
?>
