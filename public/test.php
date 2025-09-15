<?php
require __DIR__ . '/../includes/db.php';

$stmt = $pdo->query("SELECT * FROM queries");
$users = $stmt->fetchAll();

echo "<pre>";
print_r($users);
echo "</pre>";
