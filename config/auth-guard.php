<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    // No active session — check for a remember-me cookie
    if (!empty($_COOKIE['remember_me'])) {
        require_once __DIR__ . '/db.php';

        $parts           = explode(':', $_COOKIE['remember_me'], 2);
        $cookieStudentId = $parts[0] ?? null;
        $cookieToken     = $parts[1] ?? null;

        // Cookie flags must match login.php exactly so the browser honours them
        $secureCookie = [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Strict',
        ];

        // Reject malformed cookies immediately (missing either part)
        if ($cookieStudentId === null || $cookieToken === null) {
            setcookie('remember_me', '', $secureCookie);
            header('Location: login.php');
            exit;
        }

        $tokenHash = hash('sha256', $cookieToken);

        $stmt = $pdo->prepare(
            "SELECT rt.student_id, s.full_name
             FROM remember_tokens rt
             JOIN students s ON s.student_id = rt.student_id
             WHERE rt.token_hash = ?
               AND rt.student_id = ?
               AND rt.expires_at > NOW()"
        );
        $stmt->execute([$tokenHash, (int) $cookieStudentId]);
        $row = $stmt->fetch();

        if ($row) {
            // Valid token — restore the session automatically
            session_regenerate_id(true);
            $_SESSION['student_id']   = $row['student_id'];
            $_SESSION['student_name'] = $row['full_name'];
        } else {
            // Invalid or expired token — clean up and send to login
            setcookie('remember_me', '', $secureCookie);
            header('Location: login.php');
            exit;
        }
    } else {
        header('Location: login.php');
        exit;
    }
}
