<?php
if (!isset($_SESSION)) session_start();
if (!isset($conn)) include __DIR__ . "/../config/db.php";

$user = getCurrentUser();
$greeting = 'Good Morning';
$hour = date('H');
if ($hour >= 12 && $hour < 17) $greeting = 'Good Afternoon';
elseif ($hour >= 17) $greeting = 'Good Evening';

$date_str = date('l, j F');
?>

<div class="top-header">
    <div class="header-left">
        <h3><?php echo $date_str; ?></h3>
        <h1><?php echo $greeting . ' ' . ($user['full_name'] ?? 'Guest') . ' 👋'; ?></h1>
    </div>
    <div class="header-right">
        <button class="notification" onclick="checkNotifications()">
            🔔
            <span id="notifBadge" style="display:none;background:#dc2626;color:white;border-radius:50%;width:8px;height:8px;position:absolute;top:8px;right:8px;"></span>
        </button>
        <div class="user-profile">
            <div class="user-avatar"><?php echo $user['avatar'] ?? '👵'; ?></div>
            <div>
                <strong><?php echo $user['full_name'] ?? 'Guest User'; ?></strong>
                <p style="font-size:11px;color:#94a3b8;"><?php echo ucfirst($user['role'] ?? 'Patient'); ?></p>
            </div>
        </div>
    </div>
</div>
