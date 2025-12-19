<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать работу</title>
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
    SELECT cw.*, m.first_name, m.last_name
    FROM completed_works cw
    JOIN masters m ON cw.master_id = m.id
    WHERE cw.id = :id AND cw.master_id = :master_id
");
$stmt->execute([':id' => $id, ':master_id' => $master_id]);
$work = $stmt->fetch();

if (!$work):
    header("Location: works.php?master_id=$master_id");
    exit;
endif;

$services = $pdo->query("SELECT id, name, price FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();
$clients = $pdo->query("SELECT id, first_name || ' ' || last_name AS name FROM clients ORDER BY last_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $service_id = (int)($_POST['service_id'] ?? 0);
    $client_id = (int)($_POST['client_id'] ?? 0);
    $completion_date = trim($_POST['completion_date'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$service_id || !$client_id):
        $error = 'Выберите услугу и клиента';
    elseif (empty($completion_date)):
        $error = 'Укажите дату выполнения';
    elseif ($price <= 0):
        $error = 'Укажите стоимость';
    else:
        try {
            $stmt = $pdo->prepare("
                UPDATE completed_works
                SET service_id = :service_id,
                    client_id = :client_id,
                    completion_date = :completion_date,
                    price = :price,
                    notes = :notes
                WHERE id = :id
            ");
            
            $stmt->execute([
                ':service_id' => $service_id,
                ':client_id' => $client_id,
                ':completion_date' => $completion_date,
                ':price' => $price,
                ':notes' => $notes ?: null,
                ':id' => $id
            ]);
            
            header("Location: works.php?master_id=$master_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    endif;
else:
    $_POST = $work;
endif;
?>

<div class="container">
    <header>
        <h1>Редактировать выполненную работу</h1>
        <p><?= htmlspecialchars($work['last_name'] . ' ' . $work['first_name']) ?></p>
    </header>

    <div class="content">
        <a href="works.php?master_id=<?= $master_id ?>" class="back-link">← Вернуться к списку работ</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="client_id">Клиент *</label>
                    <select id="client_id" name="client_id" required>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= $client['id'] ?>" 
                                    <?= ($_POST['client_id'] ?? '') == $client['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service_id">Услуга *</label>
                    <select id="service_id" name="service_id" required>
                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id'] ?>" 
                                    data-price="<?= $service['price'] ?>"
                                    <?= ($_POST['service_id'] ?? '') == $service['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($service['name']) ?> (<?= number_format($service['price'], 0) ?> ₽)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Стоимость (₽) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="completion_date">Дата выполнения *</label>
                    <input type="date" id="completion_date" name="completion_date" required
                           value="<?= htmlspecialchars($_POST['completion_date'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="notes">Примечание</label>
                    <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">Сохранить</button>
                    <a href="works.php?master_id=<?= $master_id ?>" class="btn btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('service_id').addEventListener('change', function() {
    const price = this.options[this.selectedIndex].dataset.price;
    if (price) {
        document.getElementById('price').value = price;
    }
});
</script>

</body>
</html>
