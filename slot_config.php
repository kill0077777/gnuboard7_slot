<?php
require_once __DIR__ . '/db.php';

function slot_setting_int(string $key, int $default, int $min, int $max): int
{
    try {
        $row = slot_db_one('SELECT setting_value FROM slot_game_settings WHERE setting_key = :setting_key LIMIT 1', [':setting_key' => $key]);
        $value = $row === null ? $default : (int)$row['setting_value'];
    } catch (Throwable $e) {
        error_log('slot setting read failed: ' . $e->getMessage());
        $value = $default;
    }
    return max($min, min($max, $value));
}

function slot_game_jackpot(): int
{
    $min = slot_setting_int('jackpot_min', 3000, 0, 100000000);
    $max = slot_setting_int('jackpot_max', 20000, $min, 100000000);
    return random_int($min, max($min, $max));
}

function slot_game_double_multiplier(): int
{
    $chance5 = slot_setting_int('double_5_chance_bp', 100, 0, 10000);
    $chance3 = slot_setting_int('double_3_chance_bp', 100, 0, 10000);
    $chance2 = slot_setting_int('double_2_chance_bp', 200, 0, 10000);
    $roll = random_int(1, 10000);
    if ($roll <= $chance5) {
        return 5;
    }
    if ($roll <= $chance5 + $chance3) {
        return 3;
    }
    if ($roll <= $chance5 + $chance3 + $chance2) {
        return 2;
    }
    return 1;
}
