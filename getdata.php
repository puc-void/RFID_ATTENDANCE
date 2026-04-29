<?php
require 'connectDB.php';
date_default_timezone_set('Asia/Dhaka'); // Bangladesh timezone

$d = date("Y-m-d");
$t = date("H:i:s");

if (isset($_GET['card_uid']) && isset($_GET['device_token'])) {

    $card_uid   = $_GET['card_uid'];
    $device_uid = $_GET['device_token'];

    // -----------------------
    // CHECK VALID DEVICE
    // -----------------------
    $sql = "SELECT * FROM devices WHERE device_uid=?";
    $stmt = mysqli_stmt_init($conn);

    if (!mysqli_stmt_prepare($stmt, $sql)) {
        echo "SQL_Error_Select_device";
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

    // -------------------------------------------------------------
    // MODE 1 → ATTENDANCE LOGIN / LOGOUT
    // -------------------------------------------------------------
    if ($device_mode == 1) {

        // Check if card belongs to a user
        $sql = "SELECT * FROM users WHERE card_uid=?";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_Select_card";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "s", $card_uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$userRow = mysqli_fetch_assoc($result)) {
            echo "Not found!";
            exit();
        }

        // Card found but not registered
        if ($userRow['add_card'] == 0) {
            echo "Not registerd!";
            exit();
        }

        // Check device permission
        if ($userRow['device_uid'] != 0 && $userRow['device_uid'] != $device_uid) {
            echo "Not Allowed!";
            exit();
        }

        $Uname  = $userRow['username'];
        $Number = $userRow['serialnumber'];

        // Check if user already logged in and not logged out yet
        $sql = "SELECT * FROM users_logs WHERE card_uid=? AND checkindate=? AND card_out=0";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_Select_logs";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "ss", $card_uid, $d);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // -------------------------------------------------
        // LOGIN
        // -------------------------------------------------
        if (!mysqli_fetch_assoc($result)) {

            $sql = "INSERT INTO users_logs
            (username, serialnumber, card_uid, device_uid, device_dep, checkindate, timein, timeout, card_out)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";

            $stmt = mysqli_stmt_init($conn);

            if (!mysqli_stmt_prepare($stmt, $sql)) {
                echo "SQL_Error_Insert_Login";
                exit();
            }

            $timeout = "00:00:00";

            mysqli_stmt_bind_param($stmt, "ssssssss",
                                   $Uname, $Number, $card_uid, $device_uid, $device_dep, $d, $t, $timeout
            );

            mysqli_stmt_execute($stmt);   // IMPORTANT

            echo "login".$Uname;
            exit();
        }

        // -------------------------------------------------
        // LOGOUT
        // -------------------------------------------------
        $sql = "UPDATE users_logs
        SET timeout=?, card_out=1
        WHERE card_uid=? AND checkindate=? AND card_out=0";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_insert_logout";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "sss", $t, $card_uid, $d);
        mysqli_stmt_execute($stmt);

        echo "logout".$Uname;
        exit();
    }



    // -------------------------------------------------------------
    // MODE 0 → REGISTER NEW CARD
    // -------------------------------------------------------------
    if ($device_mode == 0) {

        // Check if card already exists
        $sql = "SELECT * FROM users WHERE card_uid=?";
        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_Select_card";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "s", $card_uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        // IF CARD EXISTS → mark as selected
        if ($row = mysqli_fetch_assoc($result)) {

            mysqli_query($conn, "UPDATE users SET card_select=0");
            mysqli_query($conn, "UPDATE users SET card_select=1 WHERE card_uid='$card_uid'");

            echo "available";
            exit();
        }

        // NEW CARD
        mysqli_query($conn, "UPDATE users SET card_select=0");

        $sql = "INSERT INTO users (card_uid, card_select, device_uid, device_dep, user_date)
        VALUES (?, 1, ?, ?, CURDATE())";

        $stmt = mysqli_stmt_init($conn);

        if (!mysqli_stmt_prepare($stmt, $sql)) {
            echo "SQL_Error_Insert_NewCard";
            exit();
        }

        mysqli_stmt_bind_param($stmt, "sss", $card_uid, $device_uid, $device_dep);
        mysqli_stmt_execute($stmt);

        echo "succesful";
        exit();
    }
}
?>
