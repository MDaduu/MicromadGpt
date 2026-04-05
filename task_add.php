<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($title === '') {
    setFlash('error', 'عنوان المهمة مطلوب.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO tasks (user_id, title, description, status, created_at) VALUES (:user_id, :title, :description, :status, NOW())'
);
$stmt->execute([
    'user_id' => currentUserId(),
    'title' => $title,
    'description' => $description,
    'status' => 'new',
]);

setFlash('success', 'تمت إضافة المهمة بنجاح.');
header('Location: dashboard.php');
exit;
