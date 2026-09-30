<?php
// GWTTT session bootstrap.
//
// Application-level idle/absolute timeouts are enforced by header.php. PHP's
// session storage lifetime must therefore be longer than every selectable
// application timeout, and long enough that the explicit "Never" preference
// is not silently defeated by PHP's default 24-minute garbage collection.
//
// 30 days is an infrastructure retention ceiling, not a GWTTT auto-logout
// timer. Active requests refresh the session file's modification time.
const GWTTT_SESSION_STORAGE_LIFETIME = 2592000;

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', (string)GWTTT_SESSION_STORAGE_LIFETIME);

    // Keep the cookie as a browser-session cookie. GWTTT's own preference
    // controls application auto-logout; closing the browser may still end the
    // browser session depending on browser settings.
    ini_set('session.cookie_lifetime', '0');

    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}
