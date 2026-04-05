<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($name === '') {
        $errors[] = 'الاسم مطلوب.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح.';
    }

    if (mb_strlen($password) < 6) {
        $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'تأكيد كلمة المرور غير مطابق.';
    }

    if (empty($errors)) {
        $checkStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $checkStmt->execute(['email' => $email]);

        if ($checkStmt->fetch()) {
            $errors[] = 'هذا البريد مسجل مسبقًا.';
        } else {
            $insertStmt = $pdo->prepare(
                'INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :password, NOW())'
            );

            $insertStmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            setFlash('success', 'تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">إنشاء حساب جديد</h1>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label" for="name">الاسم</label>
                            <input class="form-control" id="name" name="name" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="email">البريد الإلكتروني</label>
                            <input class="form-control" id="email" name="email" type="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">كلمة المرور</label>
                            <input class="form-control" id="password" name="password" type="password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="confirm_password">تأكيد كلمة المرور</label>
                            <input class="form-control" id="confirm_password" name="confirm_password" type="password" required>
                        </div>

                        <button class="btn btn-primary w-100" type="submit">إنشاء الحساب</button>
                    </form>

                    <p class="mt-3 mb-0 text-center">
                        لديك حساب؟ <a href="login.php">تسجيل الدخول</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
