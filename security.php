<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/slot_config.php';

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
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
    $token = (string)($_POST['csrf_token'] ?? '');
    $known = (string)($_SESSION['slot_csrf'] ?? '');
    if ($known === '' || $token === '' || !hash_equals($known, $token)) {
        http_response_code(403);
        exit('Invalid request.');
    }
}

function slot_int_input(string $name, int $min, int $max, int $default = 0): int
{
    $raw = $_POST[$name] ?? null;
    if (is_array($raw) || $raw === null || filter_var($raw, FILTER_VALIDATE_INT) === false) {
        return $default;
    }
    return max($min, min($max, (int)$raw));
}

function slot_bool_input(string $name): bool
{
    $raw = $_POST[$name] ?? '';
    return $raw === true || $raw === 1 || $raw === '1' || $raw === 'true';
}

function slot_user_id(): string
{
    global $member;
    return (string)$member['user_id'];
}

function slot_fail_database(): void
{
    http_response_code(500);
    error_log('slot database operation failed');
    exit('Internal error.');
}
