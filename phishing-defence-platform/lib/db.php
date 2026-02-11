<?php
require_once __DIR__ . '/../config/config.php';

function pdo(): PDO {
  static $pdo = null;
  global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
  if ($pdo instanceof PDO) return $pdo;
  $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
  $opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $opts);
  return $pdo;
}

function db_query(string $sql, array $params = []): array {
  $st = pdo()->prepare($sql);
  $st->execute($params);
  return $st->fetchAll();
}

function db_exec(string $sql, array $params = []): int {
  $st = pdo()->prepare($sql);
  $st->execute($params);
  return $st->rowCount();
}
