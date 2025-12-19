<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Удалить мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';

if (!$id):
    header('Location: index.php');
    exit;
endif;

// Get master data
$stmt = $pdo->prepare("SELECT * FROM masters WHERE id = :id");
$stmt->execute([':id' => $id]);
$master = $stmt->fetch();

if (!$master):
    header('Location: index.php');
    exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    try {
        $stmt = $pdo->prepare("UPDATE masters SET is_active = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $error = 'Ошибка при удалении: ' . $e->getMessage();
    }
endif;
?>

<div class="container">
    <header>
        <h1>Удаление мастера</h1>
    </header>

    <div class="content">
        <a href="index.php" class="back-link">← Вернуться к списку</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="delete-confirm">
            <h2>⚠️ Подтверждение удаления</h2>
            <p>
                Вы действительно хотите удалить мастера<br>
                <strong><?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?></strong>?
            </p>

            <form method="POST" action="">
                <div class="form-actions">
                    <button type="submit" class="btn btn-delete">Удалить</button>
                    <a href="index.php" class="btn btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
