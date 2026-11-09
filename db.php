<?php
declare(strict_types=1);

final class SlotDatabaseException extends RuntimeException
{
}

function slot_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = defined('SLOT_DB_DSN') ? (string)SLOT_DB_DSN : (string)(getenv('SLOT_DB_DSN') ?: '');
    $user = defined('SLOT_DB_USER') ? (string)SLOT_DB_USER : (string)(getenv('SLOT_DB_USER') ?: '');
    $password = defined('SLOT_DB_PASSWORD') ? (string)SLOT_DB_PASSWORD : (string)(getenv('SLOT_DB_PASSWORD') ?: '');

    if ($dsn === '' && defined('G5_MYSQL_HOST')) {
        $host = (string)G5_MYSQL_HOST;
        $port = defined('G5_MYSQL_PORT') ? (string)G5_MYSQL_PORT : '3306';
        $database = defined('G5_MYSQL_DB') ? (string)G5_MYSQL_DB : '';
        $user = defined('G5_MYSQL_USER') ? (string)G5_MYSQL_USER : $user;
        $password = defined('G5_MYSQL_PASSWORD') ? (string)G5_MYSQL_PASSWORD : $password;
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4';
    }

    if ($dsn === '') {
        $host = (string)(getenv('SLOT_DB_HOST') ?: '127.0.0.1');
        $port = (string)(getenv('SLOT_DB_PORT') ?: '3306');
        $database = (string)(getenv('SLOT_DB_NAME') ?: '');
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=utf8mb4';
    }

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        error_log('slot PDO connection failed: ' . $e->getMessage());
        throw new SlotDatabaseException('Database connection failed.', 0, $e);
    }

    return $pdo;
}

function slot_db_statement(string $sql, array $params = []): PDOStatement
{
    try {
        $statement = slot_db()->prepare($sql);
        $statement->execute($params);
        return $statement;
    } catch (Throwable $e) {
        error_log('slot PDO query failed: ' . $e->getMessage());
        throw new SlotDatabaseException('Database operation failed.', 0, $e);
    }
}

function slot_db_one(string $sql, array $params = []): ?array
{
    $row = slot_db_statement($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function slot_db_all(string $sql, array $params = []): array
{
    return slot_db_statement($sql, $params)->fetchAll();
}

function slot_db_exec(string $sql, array $params = []): int
{
    return slot_db_statement($sql, $params)->rowCount();
}

function slot_db_transaction(callable $callback)
{
    $pdo = slot_db();
    $pdo->beginTransaction();
    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function slot_db_exec_script(string $path): void
{
    $sql = @file_get_contents($path);
    if ($sql === false || trim($sql) === '') {
        throw new SlotDatabaseException('SQL file could not be read.');
    }
    try {
        $sql = preg_replace('/^\s*(?:--|#).*$/m', '', $sql) ?? $sql;
        $statements = preg_split('/;\s*(?=\S)/', $sql) ?: [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement !== '' && !preg_match('/^(--|#)/', $statement)) {
                slot_db()->exec($statement);
            }
        }
    } catch (Throwable $e) {
        error_log('slot PDO script failed: ' . $e->getMessage());
        throw new SlotDatabaseException('Database script failed.', 0, $e);
    }
}

function slot_identifier(string $identifier): string
{
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
        throw new InvalidArgumentException('Invalid SQL identifier.');
    }
    return '`' . $identifier . '`';
}
