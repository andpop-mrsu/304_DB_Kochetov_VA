<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>График работы</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/db.php';

$master_id = (int)($_GET['master_id'] ?? 0);

if (!$master_id):
    header('Location: index.php');
    exit;
endif;

// Get master info
$stmt = $pdo->prepare("SELECT first_name, last_name FROM masters WHERE id = :id");
$stmt->execute([':id' => $master_id]);
$master = $stmt->fetch();

if (!$master):
    header('Location: index.php');
    exit;
endif;

// Get schedules
$stmt = $pdo->prepare("
    SELECT id, day_of_week, start_time, end_time
    FROM master_schedules
    WHERE master_id = :master_id AND is_active = 1
    ORDER BY day_of_week, start_time
");
$stmt->execute([':master_id' => $master_id]);
$schedules = $stmt->fetchAll();

$days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
?>

<div class="container">
    <header>
        <h1>График работы мастера</h1>
        <p><?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?></p>
    </header>

    <div class="content">
        <a href="index.php" class="back-link">← Вернуться к списку мастеров</a>

        <table class="masters-table">
            <thead>
                <tr>
                    <th>День недели</th>
                    <th>Начало работы</th>
                    <th>Конец работы</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($schedules)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px;">
                            График работы не установлен
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?= $days[$schedule['day_of_week']] ?></td>
                            <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                            <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                            <td class="actions">
                                <a href="edit_schedule.php?id=<?= $schedule['id'] ?>&master_id=<?= $master_id ?>" 
                                   class="btn btn-edit">Редактировать</a>
                                <a href="delete_schedule.php?id=<?= $schedule['id'] ?>&master_id=<?= $master_id ?>" 
                                   class="btn btn-delete">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="add-button">
            <a href="add_schedule.php?master_id=<?= $master_id ?>" class="btn btn-add">+ Добавить график</a>
        </div>
    </div>
</div>

</body>
</html>
