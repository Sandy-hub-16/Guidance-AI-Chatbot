<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $course   = trim($_POST['course'] ?? '');
    $year     = (int) ($_POST['year_level'] ?? 0);

    if ($fullName === '' || $email === '' || $password === '') {
        $errors[] = 'Please fill in all required fields.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO students (full_name, email, password_hash, course, year_level)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$fullName, $email, $hash, $course, $year]);

        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account — Guidance Chat</title>
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
            padding: 2rem 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(47, 111, 98, 0.11) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(212, 149, 106, 0.09) 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 560px;
            background: var(--card);
            border-radius: 24px;
            box-shadow: 0 20px 56px rgba(26, 60, 52, 0.13), 0 4px 12px rgba(26, 60, 52, 0.07);
            padding: 48px 44px;
            position: relative;
            z-index: 1;
        }

        .top-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--emerald);
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            color: white;
        }

        .brand-name {
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            color: var(--emerald);
            letter-spacing: -0.01em;
        }

        h1 {
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 1.45rem;
            color: var(--text);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 28px;
        }

        .alert-error {
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            background: #FEF2EC;
            color: #C45B3F;
            border: 1px solid #F5C9B8;
            margin-bottom: 18px;
        }

        .alert-error p+p {
            margin-top: 4px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
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

        .field input,
        .field select {
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
            appearance: none;
            -webkit-appearance: none;
        }

        .field input:focus,
        .field select:focus {
            border-color: var(--emerald);
            box-shadow: 0 0 0 3px rgba(47, 111, 98, 0.12);
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 20px 0;
            position: relative;
        }

        .divider span {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--card);
            padding: 0 10px;
            font-size: 0.72rem;
            color: var(--muted);
            font-weight: 500;
            letter-spacing: 0.06em;
            text-transform: uppercase;
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
            margin-top: 6px;
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

        @media (max-width: 520px) {
            .container {
                padding: 36px 24px;
                border-radius: 20px;
            }

            .field-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="top-brand">
            <div class="brand-icon">💬</div>
            <span class="brand-name">Guidance Chat</span>
        </div>

        <h1>Create your account</h1>
        <p class="subtitle">Join to access your personal guidance space.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>

            <div class="field">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Juan dela Cruz" required>
            </div>

            <div class="field">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 8 characters" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                </div>
            </div>

            <div class="divider"><span>Academic info (optional)</span></div>

            <div class="field-row">
                <div class="field">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course" placeholder="e.g. BSIT">
                </div>
                <div class="field">
                    <label for="year_level">Year level</label>
                    <input type="number" id="year_level" name="year_level" placeholder="1–5" min="1" max="5">
                </div>
            </div>

            <button type="submit" class="btn-primary">Create account</button>
        </form>

        <p class="footer-link">Already have an account? <a href="login.php">Sign in</a></p>
    </div>

</body>

</html>