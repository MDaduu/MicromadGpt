<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

redirectIfLoggedIn();

$errors = [];
$email = '';
$success = getFlash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'البريد الإلكتروني غير صالح.';
    }

    if ($password === '') {
        $errors[] = 'كلمة المرور مطلوبة.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'بيانات الدخول غير صحيحة.';
        } else {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: dashboard.php');
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
    <title>تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">تسجيل الدخول</h1>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

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
                            <label class="form-label" for="email">البريد الإلكتروني</label>
                            <input class="form-control" id="email" name="email" type="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">كلمة المرور</label>
                            <input class="form-control" id="password" name="password" type="password" required>
                        </div>

                        <button class="btn btn-primary w-100" type="submit">دخول</button>
                    </form>

                    <p class="mt-3 mb-0 text-center">
                        ليس لديك حساب؟ <a href="register.php">إنشاء حساب</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
