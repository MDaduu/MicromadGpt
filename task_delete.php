<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$taskId = (int)($_POST['task_id'] ?? 0);

if ($taskId < 1) {
    setFlash('error', 'معرف المهمة غير صالح.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
$stmt->execute([
    'id' => $taskId,
    'user_id' => currentUserId(),
]);

if ($stmt->rowCount() === 0) {
    setFlash('error', 'لم يتم العثور على المهمة أو لا تملك صلاحية حذفها.');
} else {
    setFlash('success', 'تم حذف المهمة بنجاح.');
}

header('Location: dashboard.php');
exit;
