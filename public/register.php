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
<title>Register</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
<div class="bg-white shadow rounded-lg p-8 w-full max-w-md">
    <h1 class="text-xl font-semibold mb-4">Create your account</h1>

    <?php foreach ($errors as $error): ?>
        <p class="text-red-600 text-sm mb-2"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form method="POST" class="space-y-3">
        <?= csrf_field() ?>
        <input type="text" name="full_name" placeholder="Full name" required
               class="w-full border rounded px-3 py-2">
        <input type="email" name="email" placeholder="Email" required
               class="w-full border rounded px-3 py-2">
        <input type="password" name="password" placeholder="Password (min 8 characters)" required
               class="w-full border rounded px-3 py-2">
        <input type="password" name="confirm_password" placeholder="Confirm password" required
               class="w-full border rounded px-3 py-2">
        <input type="text" name="course" placeholder="Course (e.g. BSIT)"
               class="w-full border rounded px-3 py-2">
        <input type="number" name="year_level" placeholder="Year level" min="1" max="5"
               class="w-full border rounded px-3 py-2">
        <button type="submit" class="w-full bg-blue-600 text-white rounded py-2">Register</button>
    </form>
    <p class="text-sm mt-3">Already have an account? <a href="login.php" class="text-blue-600">Log in</a></p>
</div>
</body>
</html>