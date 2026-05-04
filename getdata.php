<?php
require 'connectDB.php';
date_default_timezone_set('Asia/Dhaka');

// ================= TELEGRAM CONFIG =================
$botToken = "YOUR_BOT_TOKEN";
$chatID   = "YOUR_CHAT_ID";

function sendTelegram($msg){
    global $botToken, $chatID;

    $url = "https://api.telegram.org/bot".$botToken."/sendMessage?chat_id=".$chatID."&text=".urlencode($msg);
    file_get_contents($url);
}
// ===================================================

// Current Date & Time
$d = date("Y-m-d");
$t = date("H:i:s");

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

    if ($userRow['device_uid'] != 0 && $userRow['device_uid'] != $device_uid) {
        echo "Not Allowed!";
        exit();
    }

    $Uname  = $userRow['username'];
    $Number = $userRow['serialnumber'];

    // Check if already checked IN
    $sql = "SELECT * FROM users_logs WHERE card_uid=? AND checkindate=? AND card_out=0";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_Log";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $card_uid, $d);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // ================= IN TIME =================
    if (!mysqli_fetch_assoc($result)) {

        $sql = "INSERT INTO users_logs
        (username, serialnumber, card_uid, device_uid, device_dep, checkindate, timein, timeout, card_out)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_Insert_IN";
            exit();
        }

        $timeout = "00:00:00";

        mysqli_stmt_bind_param($stmt, "ssssssss",
            $Uname, $Number, $card_uid, $device_uid, $device_dep, $d, $t, $timeout
        );

        mysqli_stmt_execute($stmt);

        // 🔔 TELEGRAM (IN)
        $msg = "🟢 IN TIME\n"
             . "Name: $Uname\n"
             . "ID: $Number\n"
             . "Department: $device_dep\n"
             . "Time: $t\n"
             . "Date: $d";

        sendTelegram($msg);

        echo "login".$Uname;
        exit();
    }

    // ================= OUT TIME =================
    $sql = "UPDATE users_logs
            SET timeout=?, card_out=1
            WHERE card_uid=? AND checkindate=? AND card_out=0";

    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_Insert_OUT";
        exit();
    }

    mysqli_stmt_bind_param($stmt, "sss", $t, $card_uid, $d);
    mysqli_stmt_execute($stmt);

    // 🔔 TELEGRAM (OUT)
    $msg = "🔴 OUT TIME\n"
         . "Name: $Uname\n"
         . "ID: $Number\n"
         . "Department: $device_dep\n"
         . "Time: $t\n"
         . "Date: $d";

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

    // Insert new card
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

    // 🔔 TELEGRAM (NEW REGISTER)
    $msg = "🆕 NEW STUDENT REGISTERED\n"
         . "Card UID: $card_uid\n"
         . "Device: $device_uid\n"
         . "Department: $device_dep\n"
         . "Time: $t\n"
         . "Date: $d";

    sendTelegram($msg);

    echo "succesful";
    exit();
}

?>