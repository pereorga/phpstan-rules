<?php

use PDO;

function (PDO $pdo, \PDOStatement $stmt) {
    // Valid: fetch mode specified
    $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->fetchAll(PDO::FETCH_CLASS, 'Foo');

    // Valid: fetch mode specified when calling from PDO::query()
    $pdo->query('SELECT 1')->fetch(PDO::FETCH_ASSOC);
    $pdo->query('SELECT 1')->fetchAll(PDO::FETCH_ASSOC);

    // Valid: chained call
    $pdo->query('SELECT 1')->fetchAll(PDO::FETCH_KEY_PAIR);

    // More valid user examples
    $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt->fetchAll(PDO::FETCH_CLASS, 'Book');
    $pdo->query('SELECT ...')->fetchAll(PDO::FETCH_KEY_PAIR);
    $pdo->query("...")->fetch(PDO::FETCH_ASSOC);
};
