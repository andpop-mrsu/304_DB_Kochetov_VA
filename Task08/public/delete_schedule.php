<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить график</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$master_id = (int)($_GET['master_id'] ?? 0);
$error = '';

if (!$id || !$master_id):
    header('Location: index.php');
    exit;
endif;

$stmt = $pdo->prepare("SELECT * FROM master_schedules WHERE id = :id AND master_id = :master_id");
$stmt->execute([':id' => $id, ':master_id' => $master_id]);
$schedule = $stmt->fetch();

if (!$schedule):
    header("Location: schedules.php?master_id=$master_id");
    exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    try {
        $stmt = $pdo->prepare("UPDATE master_schedules SET is_active = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        header("Location: schedules.php?master_id=$master_id");
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
endif;

$days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
?>

<div class="container">
    <header>
        <h1>Удаление графика работы</h1>
    </header>

    <div class="content">
        <a href="schedules.php?master_id=<?= $master_id ?>" class="back-link">← Вернуться к графику</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="delete-confirm">
            <h2>⚠️ Подтверждение удаления</h2>
            <p>
                Вы действительно хотите удалить запись:<br>
                <strong><?= $days[$schedule['day_of_week']] ?>: <?= $schedule['start_time'] ?> - <?= $schedule['end_time'] ?></strong>
            </p>

            <form method="POST" action="">
                <div class="form-actions">
                    <button type="submit" class="btn btn-delete">Удалить</button>
                    <a href="schedules.php?master_id=<?= $master_id ?>" class="btn btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
