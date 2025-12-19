<?php
/**
 * Web Application: Barbershop Completed Works Report
 * Displays completed works with master filtering using dropdown
 * Uses alternative PHP syntax without echo statements
 */

// Database connection
define('DB_PATH', __DIR__ . '/../Task06/barbershop.db');

/**
 * Connect to SQLite database using PDO
 */
function connectDatabase(): PDO {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Get all masters from database
 */
function getAllMasters(PDO $pdo): array {
    $stmt = $pdo->query("
        SELECT id, last_name, first_name 
        FROM masters 
        WHERE is_active = 1 
        ORDER BY last_name, first_name
    ");
    return $stmt->fetchAll();
}

/**
 * Get completed works with optional master filter
 */
function getCompletedWorks(PDO $pdo, ?int $masterId = null): array {
    $sql = "
        SELECT 
            m.id AS master_id,
            m.last_name || ' ' || m.first_name AS master_name,
            cw.completion_date,
            s.name AS service_name,
            cw.price
        FROM completed_works cw
        INNER JOIN masters m ON cw.master_id = m.id
        INNER JOIN services s ON cw.service_id = s.id
    ";
    
    if ($masterId !== null):
        $sql .= " WHERE m.id = :master_id";
    endif;
    
    $sql .= " ORDER BY m.last_name, m.first_name, cw.completion_date";
    
    $stmt = $pdo->prepare($sql);
    
    if ($masterId !== null):
        $stmt->bindValue(':master_id', $masterId, PDO::PARAM_INT);
    endif;
    
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Calculate total revenue
 */
function calculateTotal(array $works): float {
    return array_sum(array_column($works, 'price'));
}

// ============================================================================
// Main logic
// ============================================================================

$pdo = connectDatabase();
$masters = getAllMasters($pdo);

// Get selected master ID from GET parameter
$selectedMasterId = null;
$selectedMasterName = 'Все мастера';

if (isset($_GET['master_id']) && $_GET['master_id'] !== ''):
    $selectedMasterId = (int)$_GET['master_id'];
    
    // Find selected master name
    foreach ($masters as $master):
        if ($master['id'] === $selectedMasterId):
            $selectedMasterName = $master['last_name'] . ' ' . $master['first_name'];
            break;
        endif;
    endforeach;
endif;

// Get completed works
$works = getCompletedWorks($pdo, $selectedMasterId);
$totalRevenue = calculateTotal($works);
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет об оказанных услугах - Парикмахерская</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .filter-section {
            padding: 25px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #333;
        }
        
        .filter-group select {
            flex: 1;
            min-width: 250px;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        
        .filter-group select:hover {
            border-color: #667eea;
        }
        
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .filter-group button {
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .filter-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .filter-group button:active {
            transform: translateY(0);
        }
        
        .content {
            padding: 30px;
        }
        
        .info-message {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #1976D2;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-data svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        tbody tr {
            transition: background-color 0.2s;
        }
        
        tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        .price {
            font-weight: 600;
            color: #4CAF50;
        }
        
        .summary {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            justify-space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .summary-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .summary-label {
            color: #666;
            font-size: 14px;
        }
        
        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }
        
        @media (max-width: 768px) {
            .container {
                border-radius: 0;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
            
            .header h1 {
                font-size: 22px;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group select,
            .filter-group button {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>💈 Отчет об оказанных услугах</h1>
            <p>Парикмахерская - Управление услугами и работами мастеров</p>
        </div>
        
        <div class="filter-section">
            <form method="GET" action="" class="filter-group">
                <label for="master_id">Выберите мастера:</label>
                <select name="master_id" id="master_id">
                    <option value="">Все мастера</option>
                    <?php foreach ($masters as $master): ?>
                        <option value="<?= $master['id'] ?>" 
                                <?= $selectedMasterId === $master['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($master['last_name'] . ' ' . $master['first_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Показать</button>
            </form>
        </div>
        
        <div class="content">
            <?php if ($selectedMasterId !== null): ?>
                <div class="info-message">
                    <strong>Фильтр активен:</strong> Показаны работы мастера 
                    <?= htmlspecialchars($selectedMasterName) ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($works)): ?>
                <div class="no-data">
                    <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <h2>Данные отсутствуют</h2>
                    <p>Нет выполненных работ для отображения</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Мастера</th>
                            <th>ФИО Мастера</th>
                            <th>Дата работы</th>
                            <th>Услуга</th>
                            <th>Стоимость</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($works as $work): ?>
                            <tr>
                                <td><?= $work['master_id'] ?></td>
                                <td><?= htmlspecialchars($work['master_name']) ?></td>
                                <td><?= $work['completion_date'] ?></td>
                                <td><?= htmlspecialchars($work['service_name']) ?></td>
                                <td class="price"><?= number_format($work['price'], 2, '.', ' ') ?> ₽</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="summary">
                    <div class="summary-item">
                        <span class="summary-label">Всего записей:</span>
                        <span class="summary-value"><?= count($works) ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Общая выручка:</span>
                        <span class="summary-value"><?= number_format($totalRevenue, 2, '.', ' ') ?> ₽</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
