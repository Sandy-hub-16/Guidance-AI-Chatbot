<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Delete the remember-me token from DB so it can't be reused after logout
if (!empty($_COOKIE['remember_me'])) {
    [$cookieStudentId, $cookieToken] = explode(':', $_COOKIE['remember_me'], 2) + [null, null];
    $tokenHash = hash('sha256', $cookieToken ?? '');

    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE token_hash = ? AND student_id = ?");
    $stmt->execute([$tokenHash, (int) $cookieStudentId]);

    setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict']);
}

session_unset();
session_destroy();
header('Location: login.php');
exit;