<?php  
// Connect to database
require 'connectDB.php';
date_default_timezone_set('Asia/Dhaka');

// ================= TELEGRAM HELPER =================
function sendTelegram($msg) {
    global $conn;
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

// Add user
if (isset($_POST['Add'])) {
     
    $user_id = $_POST['user_id'];
    $Uname   = trim($_POST['name'] ?? '');
    $Number  = trim($_POST['number'] ?? '');
    $Phone   = trim($_POST['phone'] ?? '');
    $Email   = trim($_POST['email'] ?? '');
    $dev_uid = $_POST['dev_uid'] ?? '0';
    $Gender  = $_POST['gender'] ?? 'Male';
    
    // Check if there is any selected user
    $sql = "SELECT add_card, card_uid FROM users WHERE id=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
      echo "SQL_Error";
      exit();
    }
    else{
        mysqli_stmt_bind_param($result, "i", $user_id);
        mysqli_stmt_execute($result);
        $resultl = mysqli_stmt_get_result($result);
        if ($userCardRow = mysqli_fetch_assoc($resultl)) {

            if ($userCardRow['add_card'] == 0) {
                $card_uid = $userCardRow['card_uid'];

                if (!empty($Uname) && !empty($Number) && !empty($Email)) {
                    // Check if any user already has the Serial Number
                    $sql = "SELECT serialnumber FROM users WHERE serialnumber=? AND id NOT LIKE ?";
                    $result = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($result, $sql)) {
                        echo "SQL_Error";
                        exit();
                    }
                    else{
                        mysqli_stmt_bind_param($result, "di", $Number, $user_id);
                        mysqli_stmt_execute($result);
                        $resultl = mysqli_stmt_get_result($result);
                        if (!$row = mysqli_fetch_assoc($resultl)) {
                            $sql = "SELECT device_dep FROM devices WHERE device_uid=?";
                            $result = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($result, $sql)) {
                                echo "SQL_Error";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "s", $dev_uid);
                                mysqli_stmt_execute($result);
                                $resultl = mysqli_stmt_get_result($result);
                                if ($row = mysqli_fetch_assoc($resultl)) {
                                    $dev_name = $row['device_dep'];
                                }
                                else{
                                    $dev_name = "All";
                                }
                            }
                            $phoneVal = ($Phone !== '') ? $Phone : 'None';
                            $sql = "UPDATE users SET username=?, serialnumber=?, gender=?, phone=?, email=?, user_date=CURDATE(), device_uid=?, device_dep=?, add_card=1 WHERE id=?";
                            $result = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($result, $sql)) {
                                echo "SQL_Error_select_Fingerprint";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "sdsssssi", $Uname, $Number, $Gender, $phoneVal, $Email, $dev_uid, $dev_name, $user_id);
                                mysqli_stmt_execute($result);

                                // Send Telegram notification after registering student
                                $registeredAt = date("d M Y, h:i:s A");
                                $phoneDisplay = ($Phone !== '' && $Phone !== 'None') ? $Phone : 'N/A';
                                $msg = "🎉 NEW STUDENT REGISTERED\n"
                                     . "━━━━━━━━━━━━━━━━━━━━━\n"
                                     . "👤 Name: $Uname\n"
                                     . "🆔 Student ID: $Number\n"
                                     . "💳 RFID Card UID: $card_uid\n"
                                     . "🏫 Department: $dev_name\n"
                                     . "👫 Gender: $Gender\n"
                                     . "📞 Phone: $phoneDisplay\n"
                                     . "📧 Email: $Email\n"
                                     . "⏰ Registered At: $registeredAt";
                                sendTelegram($msg);

                                echo 1;
                                exit();
                            }
                        }
                        else {
                            echo "The serial number is already taken!";
                            exit();
                        }
                    }
                }
                else{
                    echo "Empty Fields";
                    exit();
                }
            }
            else{
                echo "This User is already exist";
                exit();
            }    
        }
        else {
            echo "There's no selected Card!";
            exit();
        }
    }
}

// Update an existing user 
if (isset($_POST['Update'])) {

    $user_id = $_POST['user_id'];
    $Uname   = trim($_POST['name'] ?? '');
    $Number  = trim($_POST['number'] ?? '');
    $Phone   = trim($_POST['phone'] ?? '');
    $Email   = trim($_POST['email'] ?? '');
    $dev_uid = $_POST['dev_uid'] ?? '0';
    $Gender  = $_POST['gender'] ?? 'Male';

    // Check if there is any selected user
    $sql = "SELECT add_card FROM users WHERE id=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
      echo "SQL_Error";
      exit();
    }
    else{
        mysqli_stmt_bind_param($result, "i", $user_id);
        mysqli_stmt_execute($result);
        $resultl = mysqli_stmt_get_result($result);
        if ($row = mysqli_fetch_assoc($resultl)) {

            if ($row['add_card'] == 0) {
                echo "First, You need to add the User!";
                exit();
            }
            else{
                if (empty($Uname) && empty($Number) && empty($Email)) {
                    echo "Empty Fields";
                    exit();
                }
                else{
                    // Check if any user already has the Serial Number
                    $sql = "SELECT serialnumber FROM users WHERE serialnumber=? AND id NOT LIKE ?";
                    $result = mysqli_stmt_init($conn);
                    if (!mysqli_stmt_prepare($result, $sql)) {
                        echo "SQL_Error";
                        exit();
                    }
                    else{
                        mysqli_stmt_bind_param($result, "di", $Number, $user_id);
                        mysqli_stmt_execute($result);
                        $resultl = mysqli_stmt_get_result($result);
                        if (!$row = mysqli_fetch_assoc($resultl)) {
                            $sql = "SELECT device_dep FROM devices WHERE device_uid=?";
                            $result = mysqli_stmt_init($conn);
                            if (!mysqli_stmt_prepare($result, $sql)) {
                                echo "SQL_Error";
                                exit();
                            }
                            else{
                                mysqli_stmt_bind_param($result, "s", $dev_uid);
                                mysqli_stmt_execute($result);
                                $resultl = mysqli_stmt_get_result($result);
                                if ($row = mysqli_fetch_assoc($resultl)) {
                                    $dev_name = $row['device_dep'];
                                }
                                else{
                                    $dev_name = "All";
                                }
                            }
                                    
                            if (!empty($Uname) && !empty($Email)) {
                                $phoneVal = ($Phone !== '') ? $Phone : 'None';
                                $sql = "UPDATE users SET username=?, serialnumber=?, gender=?, phone=?, email=?, device_uid=?, device_dep=? WHERE id=?";
                                $result = mysqli_stmt_init($conn);
                                if (!mysqli_stmt_prepare($result, $sql)) {
                                    echo "SQL_Error_select_Card";
                                    exit();
                                }
                                else{
                                    mysqli_stmt_bind_param($result, "sdsssssi", $Uname, $Number, $Gender, $phoneVal, $Email, $dev_uid, $dev_name, $user_id);
                                    mysqli_stmt_execute($result);

                                    echo 1;
                                    exit();
                                }
                            }
                        }
                        else {
                            echo "The serial number is already taken!";
                            exit();
                        }
                    }
                }
            }    
        }
        else {
            echo "There's no selected User to be updated!";
            exit();
        }
    }
}

// Select card 
if (isset($_GET['select'])) {

    $card_uid = $_GET['card_uid'];

    $sql = "SELECT * FROM users WHERE card_uid=?";
    $result = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($result, $sql)) {
        echo "SQL_Error_Select";
        exit();
    }
    else{
        mysqli_stmt_bind_param($result, "s", $card_uid);
        mysqli_stmt_execute($result);
        $resultl = mysqli_stmt_get_result($result);
        header('Content-Type: application/json');
        $data = array();
        if ($row = mysqli_fetch_assoc($resultl)) {
            foreach ($resultl as $row) {
                $data[] = $row;
            }
        }
        $resultl->close();
        $conn->close();
        print json_encode($data);
    } 
}

// Delete user 
if (isset($_POST['delete'])) {

    $user_id = $_POST['user_id'];

    if (empty($user_id)) {
        echo "There no selected user to remove";
        exit();
    } else {
        $sql = "DELETE FROM users WHERE id=?";
        $result = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($result, $sql)) {
            echo "SQL_Error_delete";
            exit();
        }
        else{
            mysqli_stmt_bind_param($result, "i", $user_id);
            mysqli_stmt_execute($result);
            echo 1;
            exit();
        }
    }
}
?>
