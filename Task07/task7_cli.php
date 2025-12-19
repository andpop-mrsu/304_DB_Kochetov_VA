<?php
/**
 * CLI Application: Barbershop Completed Works Report
 * Displays a formatted table of all services provided by masters
 * with optional filtering by master ID
 */

// Database connection
define('DB_PATH', __DIR__ . '/../Task06/barbershop.db');

/**
 * Connect to SQLite database using PDO
 * @return PDO
 */
function connectDatabase(): PDO {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage() . "\n");
    }
}

/**
 * Get all masters from database
 * @param PDO $pdo
 * @return array
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
 * @param PDO $pdo
 * @param int|null $masterId
 * @return array
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
    
    if ($masterId !== null) {
        $sql .= " WHERE m.id = :master_id";
    }
    
    $sql .= " ORDER BY m.last_name, m.first_name, cw.completion_date";
    
    $stmt = $pdo->prepare($sql);
    
    if ($masterId !== null) {
        $stmt->bindValue(':master_id', $masterId, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Draw horizontal line for table
 * @param array $columnWidths
 * @param string $left
 * @param string $middle
 * @param string $right
 */
function drawLine(array $columnWidths, string $left, string $middle, string $right): void {
    echo $left;
    $first = true;
    foreach ($columnWidths as $width) {
        if (!$first) {
            echo $middle;
        }
        echo str_repeat('─', $width + 2);
        $first = false;
    }
    echo $right . "\n";
}

/**
 * Draw table row
 * @param array $columns
 * @param array $columnWidths
 */
function drawRow(array $columns, array $columnWidths): void {
    echo '│';
    $i = 0;
    foreach ($columns as $column) {
        $width = $columnWidths[$i];
        // Use mb_strlen and mb_str_pad for correct Unicode handling
        $padLength = $width - mb_strlen($column, 'UTF-8');
        echo ' ' . $column . str_repeat(' ', $padLength + 1) . '│';
        $i++;
    }
    echo "\n";
}

/**
 * Calculate column widths based on content
 * @param array $data
 * @param array $headers
 * @return array
 */
function calculateColumnWidths(array $data, array $headers): array {
    $widths = array_map('mb_strlen', $headers);
    
    foreach ($data as $row) {
        $values = array_values($row);
        for ($i = 0; $i < count($values); $i++) {
            $len = mb_strlen((string)$values[$i], 'UTF-8');
            if ($len > $widths[$i]) {
                $widths[$i] = $len;
            }
        }
    }
    
    return $widths;
}

/**
 * Display completed works in a formatted table
 * @param array $works
 */
function displayTable(array $works): void {
    if (empty($works)) {
        echo "\nНет данных для отображения.\n\n";
        return;
    }
    
    $headers = ['ID Мастера', 'ФИО Мастера', 'Дата работы', 'Услуга', 'Стоимость (₽)'];
    
    // Prepare data for display
    $tableData = [];
    foreach ($works as $work) {
        $tableData[] = [
            $work['master_id'],
            $work['master_name'],
            $work['completion_date'],
            $work['service_name'],
            number_format($work['price'], 2, '.', ' ')
        ];
    }
    
    $columnWidths = calculateColumnWidths($tableData, $headers);
    
    echo "\n";
    drawLine($columnWidths, '┌', '┬', '┐');
    drawRow($headers, $columnWidths);
    drawLine($columnWidths, '├', '┼', '┤');
    
    foreach ($tableData as $row) {
        drawRow($row, $columnWidths);
    }
    
    drawLine($columnWidths, '└', '┴', '┘');
    echo "\n";
    echo "Всего записей: " . count($works) . "\n\n";
}

/**
 * Display available masters and prompt for selection
 * @param array $masters
 * @return int|null
 */
function selectMaster(array $masters): ?int {
    echo "\n=== Доступные мастера ===\n\n";
    
    foreach ($masters as $master) {
        printf("%d. %s %s\n", 
            $master['id'], 
            $master['last_name'], 
            $master['first_name']
        );
    }
    
    echo "\nВведите ID мастера для фильтрации или нажмите Enter для вывода всех: ";
    $input = trim(fgets(STDIN));
    
    if ($input === '') {
        return null;
    }
    
    // Validate input
    if (!is_numeric($input)) {
        echo "Ошибка: ID должен быть числом.\n";
        return selectMaster($masters);
    }
    
    $masterId = (int)$input;
    
    // Check if master exists
    $validIds = array_column($masters, 'id');
    if (!in_array($masterId, $validIds)) {
        echo "Ошибка: Мастер с ID $masterId не найден.\n";
        return selectMaster($masters);
    }
    
    return $masterId;
}

// ============================================================================
// Main program
// ============================================================================

try {
    // Connect to database
    $pdo = connectDatabase();
    
    // Get all masters
    $masters = getAllMasters($pdo);
    
    if (empty($masters)) {
        die("В базе данных нет активных мастеров.\n");
    }
    
    // Display header
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════╗\n";
    echo "║         Отчет об оказанных услугах - Парикмахерская             ║\n";
    echo "╚══════════════════════════════════════════════════════════════════╝\n";
    
    // Select master (or all)
    $masterId = selectMaster($masters);
    
    // Get completed works
    $works = getCompletedWorks($pdo, $masterId);
    
    // Display results
    if ($masterId !== null) {
        $selectedMaster = array_filter($masters, fn($m) => $m['id'] === $masterId);
        $selectedMaster = reset($selectedMaster);
        echo "\n--- Фильтр: " . $selectedMaster['last_name'] . ' ' . $selectedMaster['first_name'] . " (ID: $masterId) ---\n";
    } else {
        echo "\n--- Показаны все мастера ---\n";
    }
    
    displayTable($works);
    
} catch (Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
    exit(1);
}
