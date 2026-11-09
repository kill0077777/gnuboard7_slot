<?php
require_once __DIR__ . '/zbpath.php';
require_once $_zb_path . '_head.php';
require_once __DIR__ . '/security.php';
slot_require_login();
if (empty($member['is_admin']) || (string)$member['is_admin'] !== '1') {
    http_response_code(403);
    exit('Admin only.');
}

function admin_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function admin_fail(string $message, int $status = 400): void
{
    http_response_code($status);
    exit($message);
}

$setting_rules = [
    'bet_min' => [1, 1000, 20],
    'bet_max' => [20, 100000, 300],
    'jackpot_min' => [0, 100000000, 3000],
    'jackpot_max' => [0, 100000000, 20000],
    'double_5_chance_bp' => [0, 10000, 100],
    'double_3_chance_bp' => [0, 10000, 100],
    'double_2_chance_bp' => [0, 10000, 200],
    'payout_multiplier_bp' => [0, 100000, 10000],
];

$message = '';
$error = '';
$admin_user_id = (string)$member['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    slot_require_csrf();
    $action = (string)($_POST['action'] ?? '');
    try {
        if ($action === 'save_settings') {
            $values = [];
            foreach ($setting_rules as $key => [$min, $max, $default]) {
                $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
                if ($value === false || $value < $min || $value > $max) {
                    admin_fail('설정값 범위를 확인하세요.');
                }
                $values[$key] = (int)$value;
            }
            if ($values['bet_max'] < $values['bet_min'] || $values['jackpot_max'] < $values['jackpot_min']) {
                admin_fail('최대값은 최소값보다 크거나 같아야 합니다.');
            }
            $chance_sum = $values['double_5_chance_bp'] + $values['double_3_chance_bp'] + $values['double_2_chance_bp'];
            if ($chance_sum > 10000) {
                admin_fail('배수 확률 합계는 10000bp(100%)를 넘을 수 없습니다.');
            }
            slot_db_transaction(function (PDO $pdo) use ($values, $admin_user_id): void {
                $statement = $pdo->prepare(
                    'INSERT INTO slot_game_settings (setting_key, setting_value, updated_by)
                     VALUES (:setting_key, :setting_value, :updated_by)
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_by = VALUES(updated_by)'
                );
                foreach ($values as $key => $value) {
                    $statement->execute([':setting_key' => $key, ':setting_value' => (string)$value, ':updated_by' => $admin_user_id]);
                }
            });
            $message = '게임 확률·배당 설정을 저장했습니다.';
        } elseif ($action === 'admin_charge' || $action === 'admin_gift') {
            $target = trim((string)($_POST['target_user_id'] ?? ''));
            $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_INT);
            $memo = trim((string)($_POST['memo'] ?? ''));
            if ($target === '' || !preg_match('/^[A-Za-z0-9_.@-]{1,80}$/', $target)) {
                admin_fail('올바른 회원 아이디를 입력하세요.');
            }
            if ($amount === false || $amount < 1 || $amount > 100000000 || strlen($memo) > 255) {
                admin_fail('포인트 또는 메모 값을 확인하세요.');
            }
            slot_db_transaction(function (PDO $pdo) use ($target, $amount, $memo, $action, $admin_user_id): void {
                $lock = $pdo->prepare('SELECT gamepoint FROM zetyx_member_table WHERE user_id = :user_id FOR UPDATE');
                $lock->execute([':user_id' => $target]);
                $row = $lock->fetch();
                if (!$row) {
                    throw new RuntimeException('Target member not found.');
                }
                $after = max(0, (int)$row['gamepoint']) + (int)$amount;
                $update = $pdo->prepare('UPDATE zetyx_member_table SET gamepoint = :gamepoint WHERE user_id = :user_id');
                $update->execute([':gamepoint' => $after, ':user_id' => $target]);
                $ledger = $pdo->prepare(
                    'INSERT INTO slot_point_ledger
                     (user_id, actor_user_id, amount, balance_after, point_type, reference_key, memo)
                     VALUES (:user_id, :actor_user_id, :amount, :balance_after, :point_type, :reference_key, :memo)'
                );
                $ledger->execute([
                    ':user_id' => $target,
                    ':actor_user_id' => $admin_user_id,
                    ':amount' => (int)$amount,
                    ':balance_after' => $after,
                    ':point_type' => $action === 'admin_gift' ? 'admin_gift' : 'admin_charge',
                    ':reference_key' => 'admin:' . $admin_user_id . ':' . bin2hex(random_bytes(12)),
                    ':memo' => $memo,
                ]);
            });
            $message = '회원 포인트 처리가 완료되었습니다.';
        } else {
            admin_fail('지원하지 않는 관리자 작업입니다.');
        }
    } catch (Throwable $e) {
        if ($e instanceof SlotDatabaseException) {
            $error = 'DB 처리에 실패했습니다. 서버 로그를 확인하세요.';
        } else {
            $error = $e->getMessage();
        }
    }
}

$settings = [];
foreach ($setting_rules as $key => [$min, $max, $default]) {
    $settings[$key] = slot_setting_int($key, $default, $min, $max);
}
$search = trim((string)($_GET['q'] ?? ''));
$members = $search === '' ? [] : slot_db_all(
    'SELECT user_id, name, nick_name, gamepoint FROM zetyx_member_table
      WHERE user_id LIKE :query OR name LIKE :query OR nick_name LIKE :query
      ORDER BY user_id ASC LIMIT 50',
    [':query' => '%' . $search . '%']
);
$selected_user_id = trim((string)($_GET['user_id'] ?? ''));
$selected_member = null;
$history = [];
if ($selected_user_id !== '' && preg_match('/^[A-Za-z0-9_.@-]{1,80}$/', $selected_user_id)) {
    $selected_member = slot_db_one(
        'SELECT user_id, name, nick_name, gamepoint FROM zetyx_member_table WHERE user_id = :user_id LIMIT 1',
        [':user_id' => $selected_user_id]
    );
    $history = slot_db_all(
        'SELECT actor_user_id, amount, balance_after, point_type, memo, created_at
           FROM slot_point_ledger WHERE user_id = :user_id ORDER BY ledger_id DESC LIMIT 100',
        [':user_id' => $selected_user_id]
    );
}
$csrf = admin_escape(slot_csrf_token());
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>슬롯 관리자</title>
<style>
body { margin:0; background:#f8fafc; color:#1f2937; font-family:Arial,sans-serif; }
.wrap { width:min(1120px,calc(100% - 32px)); margin:28px auto; }
.grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:16px; }
.card { padding:22px; background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 8px 24px rgba(15,23,42,.05); margin-bottom:16px; }
h1,h2 { margin-top:0; } label { display:block; margin:10px 0 5px; font-size:13px; font-weight:700; }
input,textarea { width:100%; box-sizing:border-box; padding:9px; border:1px solid #d1d5db; border-radius:7px; font:inherit; }
button { margin-top:12px; padding:10px 14px; border:0; border-radius:7px; background:#7c3aed; color:#fff; font-weight:700; cursor:pointer; }
button.secondary { background:#475569; } .notice,.error { padding:11px; border-radius:8px; margin-bottom:14px; } .notice{background:#ecfdf5;color:#166534}.error{background:#fef2f2;color:#991b1b}
.settings { display:grid; grid-template-columns:1fr 1fr; gap:10px; } .settings label { margin:0; } table { width:100%; border-collapse:collapse; font-size:13px; } th,td { padding:9px; border-bottom:1px solid #eef0f4; text-align:left; } th { background:#f8fafc; }
.scroll { overflow:auto; } .muted { color:#64748b; font-size:13px; }
</style>
</head>
<body>
<main class="wrap">
<h1>슬롯 관리자</h1>
<?php if ($message !== ''): ?><div class="notice"><?=admin_escape($message)?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="error"><?=admin_escape($error)?></div><?php endif; ?>
<div class="grid">
<section class="card"><h2>확률·배당 설정</h2><p class="muted">확률 단위는 bp이며 10000bp가 100%입니다.</p>
<form method="post" action="slot_admin.php"><input type="hidden" name="csrf_token" value="<?=$csrf?>"><input type="hidden" name="action" value="save_settings"><div class="settings">
<?php foreach ($setting_rules as $key => [$min, $max, $default]): ?><label><?=admin_escape($key)?><input type="number" name="<?=admin_escape($key)?>" min="<?=$min?>" max="<?=$max?>" value="<?=admin_escape($settings[$key])?>" required></label><?php endforeach; ?>
</div><button type="submit">설정 저장</button></form></section>
<section class="card"><h2>회원 포인트 충전·선물</h2>
<form method="post" action="slot_admin.php"><input type="hidden" name="csrf_token" value="<?=$csrf?>"><label>대상 회원 ID<input name="target_user_id" maxlength="80" required></label><label>포인트<input name="amount" type="number" min="1" max="100000000" required></label><label>메모<input name="memo" maxlength="255"></label><button name="action" value="admin_charge" type="submit">포인트 충전</button> <button class="secondary" name="action" value="admin_gift" type="submit">포인트 선물</button></form></section>
</div>
<section class="card"><h2>회원 조회 및 포인트 내역</h2><form method="get" action="slot_admin.php"><input name="q" value="<?=admin_escape($search)?>" placeholder="회원 ID, 이름, 닉네임"><button type="submit">검색</button></form>
<?php if ($search !== ''): ?><div class="scroll"><table><tr><th>ID</th><th>이름</th><th>닉네임</th><th>잔액</th><th>관리</th></tr><?php foreach ($members as $row): ?><tr><td><?=admin_escape($row['user_id'])?></td><td><?=admin_escape($row['name'])?></td><td><?=admin_escape($row['nick_name'])?></td><td><?=admin_escape(number_format((int)$row['gamepoint']))?></td><td><a href="?user_id=<?=rawurlencode($row['user_id'])?>">내역 보기</a></td></tr><?php endforeach; ?></table></div><?php endif; ?>
<?php if ($selected_member): ?><h3><?=admin_escape($selected_member['user_id'])?> 포인트 내역 (잔액 <?=admin_escape(number_format((int)$selected_member['gamepoint']))?>P)</h3><div class="scroll"><table><tr><th>일시</th><th>구분</th><th>금액</th><th>처리자</th><th>처리 후 잔액</th><th>메모</th></tr><?php foreach ($history as $row): ?><tr><td><?=admin_escape($row['created_at'])?></td><td><?=admin_escape($row['point_type'])?></td><td><?=admin_escape(number_format((int)$row['amount']))?></td><td><?=admin_escape($row['actor_user_id'])?></td><td><?=admin_escape(number_format((int)$row['balance_after']))?></td><td><?=admin_escape($row['memo'])?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
</section>
</main>
</body>
</html>
