<?php
require_once (__DIR__ . '/../header.php');

if (!isset($_SESSION['userid'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../preferences.php');
    exit();
}

$allowed = array(0, 1800, 3600, 7200, 14400, 28800);
$timeout = (int)($_POST['session_timeout_seconds'] ?? 1800);
if (!in_array($timeout, $allowed, true)) {
    $timeout = 1800;
}

$stmt = $con->prepare("INSERT INTO user_preferences (userid, session_timeout_seconds) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_timeout_seconds = VALUES(session_timeout_seconds)");
$stmt->bind_param('ii', $_SESSION['userid'], $timeout);
if ($stmt->execute()) {
    $_SESSION['session_timeout_seconds'] = $timeout;
    $_SESSION['last_activity'] = time();
    if ($timeout > 0 && !isset($_SESSION['login_time'])) $_SESSION['login_time'] = time();
    $_SESSION['preference_message'] = $timeout === 0
        ? 'Session timeout updated. GWTTT will no longer automatically sign you out.'
        : 'Session timeout updated.';
} else {
    $_SESSION['preference_message'] = 'Unable to update session timeout.';
}
$stmt->close();

header('Location: ../preferences.php');
exit();
