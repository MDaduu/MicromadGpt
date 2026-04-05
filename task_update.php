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
$status = $_POST['status'] ?? '';
$allowedStatuses = ['new', 'in_progress', 'done'];

if ($taskId < 1 || !in_array($status, $allowedStatuses, true)) {
    setFlash('error', 'بيانات التحديث غير صالحة.');
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('UPDATE tasks SET status = :status WHERE id = :id AND user_id = :user_id');
$stmt->execute([
    'status' => $status,
    'id' => $taskId,
    'user_id' => currentUserId(),
]);

if ($stmt->rowCount() === 0) {
    setFlash('error', 'لم يتم العثور على المهمة أو لا تملك صلاحية تعديلها.');
} else {
    setFlash('success', 'تم تحديث حالة المهمة.');
}

header('Location: dashboard.php');
exit;
