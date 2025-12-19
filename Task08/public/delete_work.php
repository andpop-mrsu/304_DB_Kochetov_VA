<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить работу</title>
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

$stmt = $pdo->prepare("
    SELECT cw.*, s.name AS service_name, c.first_name || ' ' || c.last_name AS client_name
    FROM completed_works cw
    JOIN services s ON cw.service_id = s.id
    JOIN clients c ON cw.client_id = c.id
    WHERE cw.id = :id AND cw.master_id = :master_id
");
$stmt->execute([':id' => $id, ':master_id' => $master_id]);
$work = $stmt->fetch();

if (!$work):
    header("Location: works.php?master_id=$master_id");
    exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    try {
        $stmt = $pdo->prepare("DELETE FROM completed_works WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        header("Location: works.php?master_id=$master_id");
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка: ' . $e->getMessage();
    }
endif;
?>

<div class="container">
    <header>
        <h1>Удаление выполненной работы</h1>
    </header>

    <div class="content">
        <a href="works.php?master_id=<?= $master_id ?>" class="back-link">← Вернуться к списку работ</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="delete-confirm">
            <h2>⚠️ Подтверждение удаления</h2>
            <p>
                Вы действительно хотите удалить запись о работе:<br><br>
                <strong>Клиент:</strong> <?= htmlspecialchars($work['client_name']) ?><br>
                <strong>Услуга:</strong> <?= htmlspecialchars($work['service_name']) ?><br>
                <strong>Дата:</strong> <?= htmlspecialchars($work['completion_date']) ?><br>
                <strong>Стоимость:</strong> <?= number_format($work['price'], 2) ?> ₽
            </p>

            <form method="POST" action="">
                <div class="form-actions">
                    <button type="submit" class="btn btn-delete">Удалить</button>
                    <a href="works.php?master_id=<?= $master_id ?>" class="btn btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
