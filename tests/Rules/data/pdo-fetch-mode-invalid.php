<?php

use PDO;

function (PDO $pdo, \PDOStatement $stmt) {
    // Invalid: no fetch mode specified
    $stmt->fetch();
    $stmt->fetchAll();

    // Invalid: fetch mode omitted when calling from PDO::query()
    $pdo->query('SELECT 1')->fetch();
    $pdo->query('SELECT 1')->fetchAll();

    // User example
    $records = $stmt->fetchAll();
};
