<?php
require __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM queries WHERE id = ?");
        $stmt->execute([$id]);
        echo "ok";
    } else {
        echo "error";
    }
}
?>