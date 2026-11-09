<?php
session_start();
if (!isset($_SESSION['Admin-name'])) { header('Location: login.php'); exit(); }
require 'connectDB.php';
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(80) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$values = array('telegram_bot_token' => '', 'telegram_chat_id' => '', 'telegram_enabled' => '0');
$saved = false; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = trim($_POST['bot_token'] ?? ''); $chatId = trim($_POST['chat_id'] ?? ''); $enabled = isset($_POST['enabled']) ? '1' : '0';
  if ($enabled === '1' && ($token === '' || $chatId === '')) $error = 'Bot token and chat ID are required to enable notifications.';
  else {
    foreach (array('telegram_bot_token' => $token, 'telegram_chat_id' => $chatId, 'telegram_enabled' => $enabled) as $key => $value) {
      $statement = mysqli_prepare($conn, 'INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
      mysqli_stmt_bind_param($statement, 'ss', $key, $value); mysqli_stmt_execute($statement);
    }
    $saved = true;
  }
}
$result = mysqli_query($conn, "SELECT setting_key, setting_value FROM app_settings WHERE setting_key IN ('telegram_bot_token', 'telegram_chat_id', 'telegram_enabled')");
if ($result) while ($row = mysqli_fetch_assoc($result)) $values[$row['setting_key']] = $row['setting_value'];
?><!doctype html><html lang="en"><head><title>Notifications — RFID Attendance</title><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="css/devices.css"><script src="js/jquery-2.2.3.min.js"></script><script src="js/bootstrap.js"></script></head><body><?php include 'header.php'; ?><main class="settings-page"><div class="settings-card"><div class="settings-heading"><div class="settings-icon"><i class="fa fa-paper-plane-o"></i></div><div><span class="eyebrow">NOTIFICATION CHANNEL</span><h1>Telegram alerts</h1><p>Receive a message whenever a student checks in or out.</p></div></div><?php if ($saved): ?><div class="settings-alert success"><i class="fa fa-check-circle"></i> Notification settings saved.</div><?php endif; ?><?php if ($error): ?><div class="settings-alert error"><i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?><form method="post" class="settings-form"><label class="switch-row"><span><strong>Enable Telegram notifications</strong><small>Send real-time RFID attendance alerts.</small></span><input type="checkbox" name="enabled" <?php echo $values['telegram_enabled'] === '1' ? 'checked' : ''; ?>><span class="switch-ui"></span></label><label for="bot_token">Bot token</label><input id="bot_token" type="password" name="bot_token" placeholder="123456789:AA..." value="<?php echo htmlspecialchars($values['telegram_bot_token']); ?>" autocomplete="new-password"><label for="chat_id">Chat ID</label><input id="chat_id" name="chat_id" placeholder="Example: 123456789 or -1001234567890" value="<?php echo htmlspecialchars($values['telegram_chat_id']); ?>"><div class="telegram-help"><i class="fa fa-info-circle"></i><span>Create a bot with <strong>@BotFather</strong>, message the bot once, then enter your bot token and chat ID here.</span></div><button type="submit" class="settings-save"><i class="fa fa-floppy-o"></i> Save notification settings</button></form></div></main></body></html>
