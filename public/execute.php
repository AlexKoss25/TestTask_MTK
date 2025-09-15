<?php
require __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo "<div class='alert alert-danger'>Не указан ID запроса</div>";
    exit;
}

// Получаем SQL-текст по ID
$stmt = $pdo->prepare("SELECT sql_text FROM queries WHERE id=?");
$stmt->execute([$id]);
$sql = $stmt->fetchColumn();

if (!$sql) {
    echo "<div class='alert alert-danger'>Запрос не найден</div>";
    exit;
}

try {
    // Выполняем SQL
    $res = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if (!$res) {
        echo "<div class='alert alert-info mt-2'>Результат пуст</div>";
        exit;
    }

    // Формируем HTML-таблицу
    echo "<table class='table table-sm table-bordered mt-2'>";
    echo "<thead><tr>";
    foreach (array_keys($res[0]) as $col) {
        echo "<th>".htmlspecialchars($col)."</th>";
    }
    echo "</tr></thead><tbody>";
    foreach ($res as $row) {
        echo "<tr>";
        foreach ($row as $val) {
            echo "<td>".htmlspecialchars($val)."</td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger mt-2'>Ошибка выполнения: ".htmlspecialchars($e->getMessage())."</div>";
}
