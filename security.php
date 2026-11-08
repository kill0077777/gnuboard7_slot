<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function slot_require_login(): void
{
    global $member;
    if (!is_array($member) || empty($member['no']) || empty($member['user_id'])) {
        http_response_code(401);
        exit('Login required.');
    }
}

function slot_csrf_token(): string
{
    if (empty($_SESSION['slot_csrf'])) {
        $_SESSION['slot_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['slot_csrf'];
}

function slot_require_csrf(): void
{
    $token = (string)($_REQUEST['csrf_token'] ?? '');
    $known = (string)($_SESSION['slot_csrf'] ?? '');
    if ($known === '' || $token === '' || !hash_equals($known, $token)) {
        http_response_code(403);
        exit('Invalid request.');
    }
}

function slot_int_input(string $name, int $min, int $max, int $default = 0): int
{
    $raw = $_REQUEST[$name] ?? null;
    if (is_array($raw) || $raw === null || filter_var($raw, FILTER_VALIDATE_INT) === false) {
        return $default;
    }
    return max($min, min($max, (int)$raw));
}

function slot_bool_input(string $name): bool
{
    $raw = $_REQUEST[$name] ?? '';
    return $raw === true || $raw === 1 || $raw === '1' || $raw === 'true';
}

function slot_user_id(): string
{
    global $member, $connect;
    return mysql_real_escape_string((string)$member['user_id'], $connect);
}

function slot_fail_database(): void
{
    http_response_code(500);
    error_log('slot database operation failed');
    exit('Internal error.');
}
