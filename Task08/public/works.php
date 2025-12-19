<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выполненные работы</title>
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

$stmt = $pdo->prepare("SELECT first_name, last_name FROM masters WHERE id = :id");
$stmt->execute([':id' => $master_id]);
$master = $stmt->fetch();

if (!$master):
    header('Location: index.php');
    exit;
endif;

$stmt = $pdo->prepare("
    SELECT cw.id, cw.completion_date, cw.price, cw.notes,
           s.name AS service_name,
           c.first_name || ' ' || c.last_name AS client_name
    FROM completed_works cw
    JOIN services s ON cw.service_id = s.id
    JOIN clients c ON cw.client_id = c.id
    WHERE cw.master_id = :master_id
    ORDER BY cw.completion_date DESC, cw.created_at DESC
");
$stmt->execute([':master_id' => $master_id]);
$works = $stmt->fetchAll();

$total = array_sum(array_column($works, 'price'));
?>

<div class="container">
    <header>
        <h1>Выполненные работы</h1>
        <p><?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?></p>
    </header>

    <div class="content">
        <a href="index.php" class="back-link">← Вернуться к списку мастеров</a>

        <table class="masters-table">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Клиент</th>
                    <th>Услуга</th>
                    <th>Стоимость</th>
                    <th>Примечание</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($works)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px;">
                            Нет выполненных работ
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($works as $work): ?>
                        <tr>
                            <td><?= htmlspecialchars($work['completion_date']) ?></td>
                            <td><?= htmlspecialchars($work['client_name']) ?></td>
                            <td><?= htmlspecialchars($work['service_name']) ?></td>
                            <td><?= number_format($work['price'], 2, '.', ' ') ?> ₽</td>
                            <td><?= htmlspecialchars($work['notes'] ?? '-') ?></td>
                            <td class="actions">
                                <a href="edit_work.php?id=<?= $work['id'] ?>&master_id=<?= $master_id ?>" 
                                   class="btn btn-edit">Редактировать</a>
                                <a href="delete_work.php?id=<?= $work['id'] ?>&master_id=<?= $master_id ?>" 
                                   class="btn btn-delete">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #f8f9fa;">
                        <td colspan="3" style="text-align: right;">Всего:</td>
                        <td><?= number_format($total, 2, '.', ' ') ?> ₽</td>
                        <td colspan="2"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="add-button">
            <a href="add_work.php?master_id=<?= $master_id ?>" class="btn btn-add">+ Добавить работу</a>
        </div>
    </div>
</div>

</body>
</html>
