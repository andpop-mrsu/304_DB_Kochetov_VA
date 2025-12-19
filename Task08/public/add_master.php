<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить мастера</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $salary_percent = (int)($_POST['salary_percent'] ?? 0);
    
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($gender) || empty($specialization)):
        $error = 'Заполните все обязательные поля';
    elseif ($salary_percent <= 0 || $salary_percent > 100):
        $error = 'Процент зарплаты должен быть от 1 до 100';
    else:
        try {
            $stmt = $pdo->prepare("
                INSERT INTO masters (first_name, last_name, phone, email, gender, specialization, salary_percent)
                VALUES (:first_name, :last_name, :phone, :email, :gender, :specialization, :salary_percent)
            ");
            
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':phone' => $phone,
                ':email' => $email ?: null,
                ':gender' => $gender,
                ':specialization' => $specialization,
                ':salary_percent' => $salary_percent
            ]);
            
            $success = 'Мастер успешно добавлен!';
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = 'Ошибка при добавлении: ' . $e->getMessage();
        }
    endif;
endif;
?>

<div class="container">
    <header>
        <h1>Добавить мастера</h1>
    </header>

    <div class="content">
        <a href="index.php" class="back-link">← Вернуться к списку</a>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">Имя *</label>
                    <input type="text" id="first_name" name="first_name" required 
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="last_name">Фамилия *</label>
                    <input type="text" id="last_name" name="last_name" required
                           value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Телефон *</label>
                    <input type="tel" id="phone" name="phone" required
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="gender">Пол *</label>
                    <select id="gender" name="gender" required>
                        <option value="">Выберите...</option>
                        <option value="M" <?= ($_POST['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Мужской</option>
                        <option value="F" <?= ($_POST['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Женский</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="specialization">Специализация *</label>
                    <select id="specialization" name="specialization" required>
                        <option value="">Выберите...</option>
                        <option value="men" <?= ($_POST['specialization'] ?? '') === 'men' ? 'selected' : '' ?>>Мужская</option>
                        <option value="women" <?= ($_POST['specialization'] ?? '') === 'women' ? 'selected' : '' ?>>Женская</option>
                        <option value="universal" <?= ($_POST['specialization'] ?? '') === 'universal' ? 'selected' : '' ?>>Универсальная</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="salary_percent">Процент зарплаты (1-100) *</label>
                    <input type="number" id="salary_percent" name="salary_percent" min="1" max="100" required
                           value="<?= htmlspecialchars($_POST['salary_percent'] ?? '40') ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-save">Сохранить</button>
                    <a href="index.php" class="btn btn-cancel">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
