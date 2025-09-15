<?php
$config = parse_ini_file(__DIR__ . '/../config/config.ini', true);

try {
    $dsn = "mysql:host={$config['database']['host']};
            dbname={$config['database']['dbname']};
            port={$config['database']['port']};
            charset=utf8mb4";

    $pdo = new PDO($dsn, $config['database']['user'], $config['database']['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
