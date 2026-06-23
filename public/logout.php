<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Delete the remember-me token from DB so it can't be reused after logout
if (!empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    $cookieStudentId = $parts[0] ?? null;
    $cookieToken     = $parts[1] ?? null;

    if ($cookieStudentId !== null && $cookieToken !== null) {
        $tokenHash = hash('sha256', $cookieToken);
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token_hash = ? AND student_id = ?");
        $stmt->execute([$tokenHash, (int) $cookieStudentId]);
    }

    // Must use the same flags as setcookie() in login.php so the browser
    // actually overwrites/removes the existing cookie.
    setcookie('remember_me', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Strict',
    ]);
}

session_unset();
session_destroy();
header('Location: login.php');
exit;
