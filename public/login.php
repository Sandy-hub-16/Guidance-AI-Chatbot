<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['student_id']   = $student['student_id'];
        $_SESSION['student_name'] = $student['full_name'];

        if ($rememberMe) {
            $token     = bin2hex(random_bytes(32)); // raw token shown to browser
            $tokenHash = hash('sha256', $token);    // only the hash is stored in DB
            $expiresAt = date('Y-m-d H:i:s', strtotime('+6 months'));

            $stmt = $pdo->prepare(
                "INSERT INTO remember_tokens (student_id, token_hash, expires_at)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$student['student_id'], $tokenHash, $expiresAt]);

            // Cookie value = student_id:raw_token so we can look it up on return
            setcookie(
                'remember_me',
                $student['student_id'] . ':' . $token,
                [
                    'expires'  => strtotime('+6 months'),
                    'path'     => '/',
                    'httponly' => true,  // JS cannot read this cookie
                    'samesite' => 'Strict',
                ]
            );
        }

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Log in</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="bg-white shadow rounded-lg p-8 w-full max-w-md">
    <h1 class="text-xl font-semibold mb-4">Log in</h1>

    <?php if (isset($_GET['registered'])): ?>
        <p class="text-green-600 text-sm mb-2">Account created. You can log in now.</p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="text-red-600 text-sm mb-2"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" class="space-y-3">
        <?= csrf_field() ?>
        <input type="email" name="email" placeholder="Email" required
               class="w-full border rounded px-3 py-2">
        <input type="password" name="password" placeholder="Password" required
               class="w-full border rounded px-3 py-2">

               <!-- This will be remember for 6 months -->
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="remember_me" class="rounded">
            Remember me
        </label>

        <button type="submit" class="w-full bg-blue-600 text-white rounded py-2">Log in</button>
    </form>
    <p class="text-sm mt-3">No account yet? <a href="register.php" class="text-blue-600">Register</a></p>
</div>
</body>
</html>