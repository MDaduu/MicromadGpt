<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$allowedStatuses = ['new', 'in_progress', 'done'];
$statusFilter = $_GET['status'] ?? 'all';
$userId = currentUserId();
$success = getFlash('success');
$error = getFlash('error');

if ($statusFilter !== 'all' && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'all';
}

$sql = 'SELECT id, title, description, status, created_at FROM tasks WHERE user_id = :user_id';
$params = ['user_id' => $userId];

if ($statusFilter !== 'all') {
    $sql .= ' AND status = :status';
    $params['status'] = $statusFilter;
}

$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

$statusLabels = [
    'new' => 'جديدة',
    'in_progress' => 'قيد التنفيذ',
    'done' => 'مكتملة',
];
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-white border-bottom mb-4">
    <div class="container">
        <span class="navbar-brand">تطبيق المهام</span>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span>مرحبًا، <?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
            <a class="btn btn-outline-danger btn-sm" href="logout.php">خروج</a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">إضافة مهمة جديدة</h2>
            <form method="post" action="task_add.php">
                <div class="mb-3">
                    <label class="form-label" for="title">عنوان المهمة</label>
                    <input class="form-control" id="title" name="title" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">الوصف</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <button class="btn btn-primary" type="submit">إضافة</button>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">قائمة المهام</h2>
        <form class="d-flex gap-2" method="get">
            <label for="status" class="visually-hidden">الحالة</label>
            <select class="form-select" id="status" name="status" onchange="this.form.submit()">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>الكل</option>
                <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>جديدة</option>
                <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>قيد التنفيذ</option>
                <option value="done" <?= $statusFilter === 'done' ? 'selected' : '' ?>>مكتملة</option>
            </select>
        </form>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="alert alert-info">لا توجد مهام لعرضها.</div>
    <?php else: ?>
        <div class="table-responsive bg-white border rounded shadow-sm">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>العنوان</th>
                    <th>الوصف</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= nl2br(htmlspecialchars((string)$task['description'], ENT_QUOTES, 'UTF-8')) ?></td>
                        <td>
                            <span class="badge text-bg-secondary">
                                <?= htmlspecialchars($statusLabels[$task['status']] ?? $task['status'], ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td>
                            <form class="d-flex gap-2" method="post" action="task_update.php">
                                <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                                <select class="form-select form-select-sm" name="status">
                                    <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                                        <option value="<?= $statusKey ?>" <?= $task['status'] === $statusKey ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-primary" type="submit">تحديث</button>
                            </form>
                            <form class="mt-2" method="post" action="task_delete.php" onsubmit="return confirm('هل أنت متأكد من حذف المهمة؟');">
                                <input type="hidden" name="task_id" value="<?= (int)$task['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">حذف</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
