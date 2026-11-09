<?php
require 'connectDB.php';
date_default_timezone_set('Asia/Dhaka');

// ================= TELEGRAM CONFIG =================
// Values are managed by telegram_settings.php. Notifications stay off until configured.
$botToken = '';
$chatID = '';
$settingsResult = @mysqli_query($conn, "SELECT setting_key, setting_value FROM app_settings WHERE setting_key IN ('telegram_bot_token', 'telegram_chat_id', 'telegram_enabled')");
if ($settingsResult) {
    $settings = array();
    while ($setting = mysqli_fetch_assoc($settingsResult)) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    if (($settings['telegram_enabled'] ?? '0') === '1') {
        $botToken = trim($settings['telegram_bot_token'] ?? '');
        $chatID = trim($settings['telegram_chat_id'] ?? '');
    }
}

function sendTelegram($msg) {
    global $botToken, $chatID;
    if ($botToken === '' || $chatID === '') {
        return;
    }
    $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'chat_id' => $chatID,
            'text' => $msg
        )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        @curl_exec($ch);
        curl_close($ch);
    } else {
        @file_get_contents($url . "?chat_id=" . urlencode($chatID) . "&text=" . urlencode($msg));
    }
}
// ===================================================

// Current Date & Time
$d = date("Y-m-d");
$t = date("H:i:s");
$formattedDateTime = date("d M Y, h:i:s A");

// ================= CHECK REQUEST =================
if (!isset($_GET['card_uid']) || !isset($_GET['device_token'])) {
    echo "Invalid Request";
    exit();
}

$card_uid   = $_GET['card_uid'];
$device_uid = $_GET['device_token'];

// ================= CHECK DEVICE =================
$sql = "SELECT * FROM devices WHERE device_uid=?";
$stmt = mysqli_stmt_init($conn);

if (!mysqli_stmt_prepare($stmt, $sql)) {
    echo "SQL_Error_Device";
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $device_uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$DeviceRow = mysqli_fetch_assoc($result)) {
    echo "Invalid Device!";
    exit();
}

$device_mode = $DeviceRow['device_mode'];
$device_dep  = $DeviceRow['device_dep'];

// =============================================================
// MODE 1 → ATTENDANCE (IN / OUT)
// =============================================================
if ($device_mode == 1) {

    // Check user
    $sql = "SELECT * FROM users WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_User";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$userRow = mysqli_fetch_assoc($result)) {
        echo "User Not Found!";
        exit();
    }

    if ($userRow['add_card'] == 0) {
        echo "Card Not Registered!";
        exit();
    }

    if ($userRow['device_uid'] != 0 && $userRow['device_uid'] != $device_uid && $userRow['device_dep'] !== $device_dep && $userRow['device_dep'] !== 'All') {
        echo "Not Allowed!";
        exit();
    }

    $Uname  = $userRow['username'];
    $Number = $userRow['serialnumber'];
    $Gender = (!empty($userRow['gender']) && $userRow['gender'] !== 'None') ? $userRow['gender'] : 'N/A';
    $Phone  = (!empty($userRow['phone']) && $userRow['phone'] !== 'None') ? $userRow['phone'] : 'N/A';
    $Email  = (!empty($userRow['email']) && $userRow['email'] !== 'None') ? $userRow['email'] : 'N/A';

    // Resolve Active Course for this Department (or global active course)
    $course_id = 0;
    $course_code = '';
    $course_title = '';
    $courseStmt = mysqli_prepare($conn, "SELECT id, course_code, course_title FROM courses WHERE (dept_name=? OR dept_name='All') ORDER BY is_active_session DESC, id ASC LIMIT 1");
    if ($courseStmt) {
        mysqli_stmt_bind_param($courseStmt, "s", $device_dep);
        mysqli_stmt_execute($courseStmt);
        $courseRes = mysqli_stmt_get_result($courseStmt);
        if ($courseRow = mysqli_fetch_assoc($courseRes)) {
            $course_id = (int)$courseRow['id'];
            $course_code = $courseRow['course_code'];
            $course_title = $courseRow['course_title'];
        }
    }

    // Check if already checked IN today for this course
    $sql = "SELECT * FROM users_logs WHERE card_uid=? AND checkindate=? AND course_id=? AND card_out=0";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_Log";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ssi", $card_uid, $d, $course_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $courseLine = ($course_code !== '') ? "📚 Course: $course_code ($course_title)\n" : "";

    // ================= IN TIME =================
    if (!mysqli_fetch_assoc($result)) {

        $sql = "INSERT INTO users_logs
        (username, serialnumber, card_uid, device_uid, device_dep, course_id, course_code, course_title, checkindate, timein, timeout, card_out)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_Insert_IN";
            exit();
        }

        $timeout = "00:00:00";

        mysqli_stmt_bind_param($stmt, "sssssisssss",
            $Uname, $Number, $card_uid, $device_uid, $device_dep, $course_id, $course_code, $course_title, $d, $t, $timeout
        );

        mysqli_stmt_execute($stmt);

        // TELEGRAM (IN)
        $msg = "✅ STUDENT CHECK IN\n"
             . "━━━━━━━━━━━━━━━━━━━━━\n"
             . "👤 Name: $Uname\n"
             . "🆔 Student ID: $Number\n"
             . "💳 RFID Card UID: $card_uid\n"
             . "🏫 Department: $device_dep\n"
             . $courseLine
             . "👫 Gender: $Gender\n"
             . "📞 Phone: $Phone\n"
             . "📧 Email: $Email\n"
             . "⏰ Check-In At: $formattedDateTime";

        sendTelegram($msg);

        echo "login".$Uname;
        exit();
    }

    // ================= OUT TIME =================
    $sql = "UPDATE users_logs
            SET timeout=?, card_out=1
            WHERE card_uid=? AND checkindate=? AND course_id=? AND card_out=0";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_Insert_OUT";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sssi", $t, $card_uid, $d, $course_id);
    mysqli_stmt_execute($stmt);

    // TELEGRAM (OUT)
    $msg = "🔴 STUDENT CHECK OUT\n"
         . "━━━━━━━━━━━━━━━━━━━━━\n"
         . "👤 Name: $Uname\n"
         . "🆔 Student ID: $Number\n"
         . "💳 RFID Card UID: $card_uid\n"
         . "🏫 Department: $device_dep\n"
         . $courseLine
         . "👫 Gender: $Gender\n"
         . "📞 Phone: $Phone\n"
         . "📧 Email: $Email\n"
         . "⏰ Check-Out At: $formattedDateTime";

    sendTelegram($msg);

    echo "logout".$Uname;
    exit();
}

// =============================================================
// MODE 0 → REGISTER NEW CARD
// =============================================================
if ($device_mode == 0) {

    // Check if already exists
    $sql = "SELECT * FROM users WHERE card_uid=?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_CheckCard";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "s", $card_uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Card already exists
    if ($row = mysqli_fetch_assoc($result)) {

        mysqli_query($conn, "UPDATE users SET card_select=0");
        mysqli_query($conn, "UPDATE users SET card_select=1 WHERE card_uid='$card_uid'");

        echo "available";
        exit();
    }

    // Insert new card (Student registration notification is sent from manage_users_conf.php once student details are saved)
    mysqli_query($conn, "UPDATE users SET card_select=0");

    $sql = "INSERT INTO users (card_uid, card_select, device_uid, device_dep, user_date)
            VALUES (?, 1, ?, ?, CURDATE())";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_InsertCard";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
    mysqli_stmt_execute($stmt);

    echo "succesful";
    exit();
}

?>
