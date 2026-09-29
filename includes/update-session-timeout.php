<?php
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ini_set('session.cookie_secure', '1');
    session_start();
}

require_once (__DIR__ . '/csrf.php');
require_once (__DIR__ . '/../connect.php');

if (!isset($_SESSION['userid'])) {
    header('Location: ../index.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../preferences.php');
    exit();
}
csrf_require_valid_post();

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
if ($con->connect_errno) die('Unable to connect to database [' . $con->connect_errno . ']');

$allowed = array(0, 1800, 3600, 7200, 14400, 28800);
$timeout = (int)($_POST['session_timeout_seconds'] ?? 1800);
if (!in_array($timeout, $allowed, true)) $timeout = 1800;

$stmt = $con->prepare("INSERT INTO user_preferences (userid, session_timeout_seconds) VALUES (?, ?) ON DUPLICATE KEY UPDATE session_timeout_seconds = VALUES(session_timeout_seconds)");
$stmt->bind_param('ii', $_SESSION['userid'], $timeout);
if ($stmt->execute()) {
    $_SESSION['session_timeout_seconds'] = $timeout;
    $_SESSION['last_activity'] = time();
    $_SESSION['preference_message'] = $timeout === 0
        ? 'Session timeout updated. GWTTT will no longer automatically sign you out.'
        : 'Session timeout updated.';
} else {
    $_SESSION['preference_message'] = 'Unable to update session timeout.';
}
$stmt->close();

header('Location: ../preferences.php');
exit();
