<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить график</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/db.php';

$master_id = (int)($_GET['master_id'] ?? 0);
$error = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $day_of_week = (int)($_POST['day_of_week'] ?? -1);
    $start_time = trim($_POST['start_time'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    
    if ($day_of_week < 0 || $day_of_week > 6):
        $error = 'Выберите день недели';
    elseif (empty($start_time) || empty($end_time)):
        $error = 'Укажите время начала и конца работы';
    elseif ($start_time >= $end_time):
        $error = 'Время окончания должно быть позже времени начала';
    else:
        try {
            $stmt = $pdo->prepare("
                INSERT INTO master_schedules (master_id, day_of_week, start_time, end_time)
                VALUES (:master_id, :day_of_week, :start_time, :end_time)
            ");
            
            $stmt->execute([
                ':master_id' => $master_id,
                ':day_of_week' => $day_of_week,
                ':start_time' => $start_time,
                ':end_time' => $end_time
            ]);
            
            header("Location: schedules.php?master_id=$master_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    endif;
endif;

$days = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
?>

<div class="container">
    <header>
        <h1>Добавить график работы</h1>
        <p><?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?></p>
    </header>

    <div class="content">
        <a href="schedules.php?master_id=<?= $master_id ?>" class="back-link">← Вернуться к графику</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="day_of_week">День недели *</label>
                    <select id="day_of_week" name="day_of_week" required>
                        <option value="">Выберите...</option>
                        <?php foreach ($days as $num => $day): ?>
                            <option value="<?= $num ?>" <?= ($_POST['day_of_week'] ?? '') == $num ? 'selected' : '' ?>>
                                <?= $day ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="start_time">Начало работы *</label>
                    <input type="time" id="start_time" name="start_time" required
                           value="<?= htmlspecialchars($_POST['start_time'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="end_time">Конец работы *</label>
                    <input type="time" id="end_time" name="end_time" required
                           value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">Сохранить</button>
                    <a href="schedules.php?master_id=<?= $master_id ?>" class="btn btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
