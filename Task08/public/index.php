<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление парикмахерской</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/db.php';

// Get all masters
$stmt = $pdo->query("
    SELECT id, first_name, last_name, specialization, phone, email
    FROM masters
    WHERE is_active = 1
    ORDER BY last_name, first_name
");
$masters = $stmt->fetchAll();
?>

<div class="container">
    <header>
        <h1>💈 Управление парикмахерской</h1>
        <p>Список мастеров</p>
    </header>

    <div class="content">
        <table class="masters-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия Имя</th>
                    <th>Специализация</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($masters as $master): ?>
                    <tr>
                        <td><?= $master['id'] ?></td>
                        <td><?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?></td>
                        <td>
                            <?php if ($master['specialization'] === 'men'): ?>
                                Мужской
                            <?php elseif ($master['specialization'] === 'women'): ?>
                                Женский
                            <?php else: ?>
                                Универсальный
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($master['phone']) ?></td>
                        <td><?= htmlspecialchars($master['email'] ?? '-') ?></td>
                        <td class="actions">
                            <a href="schedules.php?master_id=<?= $master['id'] ?>" class="btn btn-info">График</a>
                            <a href="works.php?master_id=<?= $master['id'] ?>" class="btn btn-info">Работы</a>
                            <a href="edit_master.php?id=<?= $master['id'] ?>" class="btn btn-edit">Редактировать</a>
                            <a href="delete_master.php?id=<?= $master['id'] ?>" class="btn btn-delete">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="add-button">
            <a href="add_master.php" class="btn btn-add">+ Добавить мастера</a>
        </div>
    </div>
</div>

</body>
</html>
