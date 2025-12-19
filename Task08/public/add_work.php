<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить работу</title>
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

// Get services
$services = $pdo->query("SELECT id, name, price FROM services WHERE is_active = 1 ORDER BY name")->fetchAll();

// Get clients
$clients = $pdo->query("SELECT id, first_name || ' ' || last_name AS name FROM clients ORDER BY last_name")->fetchAll();

// Get pending appointments for this master
$appointments = $pdo->prepare("
    SELECT a.id, c.first_name || ' ' || c.last_name AS client_name, a.appointment_date
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.master_id = :master_id AND a.status = 'scheduled'
    ORDER BY a.appointment_date DESC
")->execute([':master_id' => $master_id]);
$appointments = $pdo->prepare("
    SELECT a.id, c.first_name || ' ' || c.last_name AS client_name, a.appointment_date
    FROM appointments a
    JOIN clients c ON a.client_id = c.id
    WHERE a.master_id = :master_id AND a.status = 'scheduled'
    ORDER BY a.appointment_date DESC
");
$appointments->execute([':master_id' => $master_id]);
$appointments = $appointments->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
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
                INSERT INTO completed_works (appointment_id, service_id, master_id, client_id, completion_date, price, notes)
                VALUES (:appointment_id, :service_id, :master_id, :client_id, :completion_date, :price, :notes)
            ");
            
            $stmt->execute([
                ':appointment_id' => $appointment_id ?: 1, // Default to 1 if no appointment
                ':service_id' => $service_id,
                ':master_id' => $master_id,
                ':client_id' => $client_id,
                ':completion_date' => $completion_date,
                ':price' => $price,
                ':notes' => $notes ?: null
            ]);
            
            header("Location: works.php?master_id=$master_id");
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка: ' . $e->getMessage();
        }
    endif;
endif;
?>

<div class="container">
    <header>
        <h1>Добавить выполненную работу</h1>
        <p><?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?></p>
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
                        <option value="">Выберите клиента...</option>
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
                        <option value="">Выберите услугу...</option>
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
                           value="<?= htmlspecialchars($_POST['completion_date'] ?? date('Y-m-d')) ?>">
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
