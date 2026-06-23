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
            $token     = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+6 months'));

            $stmt = $pdo->prepare(
                "INSERT INTO remember_tokens (student_id, token_hash, expires_at)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$student['student_id'], $tokenHash, $expiresAt]);

            setcookie('remember_me', $student['student_id'] . ':' . $token, [
                'expires'  => strtotime('+6 months'),
                'path'     => '/',
                'httponly' => true,
                'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'samesite' => 'Strict',
            ]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — Guidance Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --forest: #1A3C34;
            --emerald: #2F6F62;
            --sage: #5BA896;
            --oat: #F5F0E8;
            --card: #FFFCF7;
            --terra: #D4956A;
            --border: #E2DAC8;
            --text: #2B2825;
            --muted: #7A7367;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--oat);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background blobs */
        body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(47, 111, 98, 0.12) 0%, transparent 70%);
            top: -200px;
            left: -200px;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212, 149, 106, 0.10) 0%, transparent 70%);
            bottom: -150px;
            right: -150px;
            pointer-events: none;
        }

        .page-layout {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            margin: 1rem;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(26, 60, 52, 0.14), 0 4px 12px rgba(26, 60, 52, 0.08);
            position: relative;
            z-index: 1;
        }

        /* Left panel */
        .brand-panel {
            background: linear-gradient(145deg, var(--forest) 0%, #234f43 60%, var(--emerald) 100%);
            color: white;
            padding: 52px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            width: 42%;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            bottom: -80px;
            right: -80px;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.04);
            top: 40px;
            right: -50px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            backdrop-filter: blur(4px);
        }

        .brand-name {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        .brand-hero {
            position: relative;
            z-index: 1;
        }

        .brand-hero h2 {
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 1.7rem;
            line-height: 1.25;
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }

        .brand-hero h2 em {
            font-style: normal;
            color: #A8DDD1;
        }

        .brand-hero p {
            font-size: 0.875rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.72);
        }

        .brand-footer {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
            position: relative;
            z-index: 1;
        }

        /* Right panel — form */
        .form-panel {
            background: var(--card);
            flex: 1;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-panel h1 {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            color: var(--text);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .form-panel .subtitle {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 28px;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            margin-bottom: 16px;
        }

        .alert-success {
            background: #EBF7F4;
            color: var(--emerald);
            border: 1px solid #C3E8E1;
        }

        .alert-error {
            background: #FEF2EC;
            color: #C45B3F;
            border: 1px solid #F5C9B8;
        }

        .field {
            margin-bottom: 14px;
        }

        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 5px;
            letter-spacing: 0.01em;
            text-transform: uppercase;
        }

        .field input {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            color: var(--text);
            background: white;
            outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }

        .field input:focus {
            border-color: var(--emerald);
            box-shadow: 0 0 0 3px rgba(47, 111, 98, 0.12);
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            margin-top: 2px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--emerald);
            cursor: pointer;
        }

        .remember-row label {
            font-size: 0.83rem;
            color: var(--muted);
            cursor: pointer;
        }

        .btn-primary {
            width: 100%;
            background: var(--emerald);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 13px;
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.18s, transform 0.12s, box-shadow 0.18s;
            box-shadow: 0 4px 14px rgba(47, 111, 98, 0.25);
            letter-spacing: -0.01em;
        }

        .btn-primary:hover {
            background: var(--forest);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(47, 111, 98, 0.30);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .footer-link {
            text-align: center;
            font-size: 0.83rem;
            color: var(--muted);
            margin-top: 18px;
        }

        .footer-link a {
            color: var(--emerald);
            font-weight: 600;
            text-decoration: none;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .brand-panel {
                display: none;
            }

            .page-layout {
                max-width: 420px;
                border-radius: 20px;
            }

            .form-panel {
                padding: 40px 28px;
            }
        }
    </style>
</head>

<body>

    <div class="page-layout">
        <!-- Left brand panel -->
        <div class="brand-panel">
            <div class="brand-logo">
                <div class="brand-icon">💬</div>
                <span class="brand-name">Guidance Chat</span>
            </div>

            <div class="brand-hero">
                <h2>A space to<br>think things <em>through.</em></h2>
                <p>Talk through what's on your mind — academic stress, personal challenges, or anything in between. Your guidance counselor's office, anytime.</p>
            </div>

            <p class="brand-footer">PUP Guidance & Counseling Office</p>
        </div>

        <!-- Right form panel -->
        <div class="form-panel">
            <h1>Welcome back</h1>
            <p class="subtitle">Sign in to continue your conversations.</p>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Account created — you can sign in now.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                <div class="remember-row">
                    <input type="checkbox" id="remember_me" name="remember_me">
                    <label for="remember_me">Keep me signed in for 6 months</label>
                </div>
                <button type="submit" class="btn-primary">Sign in</button>
            </form>

            <p class="footer-link">No account yet? <a href="register.php">Create one</a></p>
        </div>
    </div>

</body>

</html>