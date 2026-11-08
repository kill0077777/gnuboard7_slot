<?php
require_once __DIR__ . '/zbpath.php';
require_once $_zb_path . '_head.php';
require_once __DIR__ . '/security.php';

slot_require_login();

function point_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function point_fail(string $message, int $status = 400): void
{
    http_response_code($status);
    exit($message);
}

function point_member_id(string $value): string
{
    global $connect;
    return mysql_real_escape_string($value, $connect);
}

function point_balance(string $user_id): int
{
    global $connect;
    $result = mysql_query(
        "SELECT gamepoint FROM zetyx_member_table WHERE user_id='" . point_member_id($user_id) . "' LIMIT 1",
        $connect
    );
    if (!$result) {
        slot_fail_database();
    }
    $row = mysql_fetch_assoc($result);
    return $row ? max(0, (int)$row['gamepoint']) : 0;
}

function point_add_ledger(string $user_id, string $actor_user_id, int $amount, int $balance_after, string $type, string $reference, string $memo): bool
{
    global $connect;
    $sql = sprintf(
        "INSERT INTO slot_point_ledger
         (user_id, actor_user_id, amount, balance_after, point_type, reference_key, memo)
         VALUES ('%s','%s',%d,%d,'%s','%s','%s')",
        point_member_id($user_id),
        point_member_id($actor_user_id),
        $amount,
        $balance_after,
        point_member_id($type),
        point_member_id($reference),
        point_member_id($memo)
    );
    return (bool)mysql_query($sql, $connect);
}

$member_user_id = (string)$member['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    slot_require_csrf();
    $action = (string)($_POST['action'] ?? '');
    $actor_id = $member_user_id;

    if ($action === 'daily_charge') {
        $today = date('Y-m-d');
        $today_key = point_member_id($today);
        $user_key = point_member_id($member_user_id);
        $amount = 50000;

        mysql_query('START TRANSACTION', $connect);
        $claim = mysql_query(
            "INSERT INTO slot_daily_point_claims (user_id, claim_date, amount)
             VALUES ('$user_key', '$today_key', $amount)",
            $connect
        );
        if (!$claim) {
            mysql_query('ROLLBACK', $connect);
            point_fail('오늘 충전은 이미 완료되었거나 처리할 수 없습니다.', 409);
        }
        $locked = mysql_query(
            "SELECT gamepoint FROM zetyx_member_table WHERE user_id='$user_key' FOR UPDATE",
            $connect
        );
        $row = $locked ? mysql_fetch_assoc($locked) : false;
        if (!$row) {
            mysql_query('ROLLBACK', $connect);
            point_fail('회원 계정을 찾을 수 없습니다.', 404);
        }
        $after = max(0, (int)$row['gamepoint']) + $amount;
        $updated = mysql_query(
            "UPDATE zetyx_member_table SET gamepoint=$after WHERE user_id='$user_key'",
            $connect
        );
        $reference = 'daily:' . $member_user_id . ':' . $today;
        $logged = $updated && point_add_ledger($member_user_id, $actor_id, $amount, $after, 'daily_charge', $reference, '1일 무료 충전');
        if (!$logged) {
            mysql_query('ROLLBACK', $connect);
            point_fail('충전 기록 저장에 실패했습니다.', 500);
        }
        mysql_query('COMMIT', $connect);
        header('Location: point.php?charged=1');
        exit;
    }

    if ($action === 'admin_charge' || $action === 'admin_gift') {
        if (empty($member['is_admin']) || (string)$member['is_admin'] !== '1') {
            point_fail('관리자만 사용할 수 있습니다.', 403);
        }
        $target_user_id = trim((string)($_POST['target_user_id'] ?? ''));
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_INT);
        $memo = trim((string)($_POST['memo'] ?? ''));
        if ($target_user_id === '' || !preg_match('/^[A-Za-z0-9_.@-]{1,80}$/', $target_user_id)) {
            point_fail('올바른 회원 아이디를 입력하세요.');
        }
        if ($amount === false || $amount < 1 || $amount > 100000000) {
            point_fail('충전·선물 포인트는 1~100,000,000 사이여야 합니다.');
        }
        if (strlen($memo) > 255) {
            point_fail('메모는 255자 이내로 입력하세요.');
        }
        $target_key = point_member_id($target_user_id);
        mysql_query('START TRANSACTION', $connect);
        $locked = mysql_query(
            "SELECT gamepoint FROM zetyx_member_table WHERE user_id='$target_key' FOR UPDATE",
            $connect
        );
        $row = $locked ? mysql_fetch_assoc($locked) : false;
        if (!$row) {
            mysql_query('ROLLBACK', $connect);
            point_fail('대상 회원을 찾을 수 없습니다.', 404);
        }
        $after = max(0, (int)$row['gamepoint']) + (int)$amount;
        $updated = mysql_query(
            "UPDATE zetyx_member_table SET gamepoint=$after WHERE user_id='$target_key'",
            $connect
        );
        $reference = 'admin:' . $member_user_id . ':' . bin2hex(random_bytes(12));
        $type = $action === 'admin_gift' ? 'admin_gift' : 'admin_charge';
        $logged = $updated && point_add_ledger($target_user_id, $actor_id, (int)$amount, $after, $type, $reference, $memo);
        if (!$logged) {
            mysql_query('ROLLBACK', $connect);
            point_fail('포인트 기록 저장에 실패했습니다.', 500);
        }
        mysql_query('COMMIT', $connect);
        header('Location: point.php?admin_done=1');
        exit;
    }
    point_fail('지원하지 않는 요청입니다.');
}

$balance = point_balance($member_user_id);
$charged = isset($_GET['charged']);
$admin_done = isset($_GET['admin_done']);
$csrf = point_escape(slot_csrf_token());
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>통합 포인트</title>
<style>
body { margin: 0; background: #f8fafc; color: #1f2937; font-family: Arial, sans-serif; }
.point-wrap { width: min(760px, calc(100% - 32px)); margin: 32px auto; }
.card { margin-bottom: 16px; padding: 24px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 8px 24px rgba(15,23,42,.05); }
h1, h2 { margin-top: 0; }
.balance { font-size: 32px; font-weight: 700; color: #6d28d9; }
.notice { padding: 12px; margin-bottom: 16px; border-radius: 9px; background: #ecfdf5; color: #166534; }
label { display: block; margin: 12px 0 6px; font-size: 13px; font-weight: 700; }
input, textarea { box-sizing: border-box; width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font: inherit; }
button { margin-top: 14px; padding: 10px 15px; border: 0; border-radius: 8px; background: #7c3aed; color: #fff; cursor: pointer; font-weight: 700; }
button.secondary { background: #475569; }
small { color: #64748b; }
</style>
</head>
<body>
<main class="point-wrap">
  <h1>통합 포인트</h1>
<?php if ($charged || $admin_done): ?><div class="notice">포인트 처리가 완료되었습니다.</div><?php endif; ?>
  <section class="card">
    <h2>현재 잔액</h2>
    <div class="balance"><?=point_escape(number_format($balance))?> P</div>
    <small>게임포인트와 그누보드 포인트를 하나의 잔액으로 사용합니다.</small>
  </section>
  <section class="card">
    <h2>회원 무료 충전</h2>
    <p>하루 1회 50,000포인트를 충전할 수 있습니다.</p>
    <form method="post" action="point.php">
      <input type="hidden" name="csrf_token" value="<?=$csrf?>">
      <input type="hidden" name="action" value="daily_charge">
      <button type="submit">오늘 50,000P 충전</button>
    </form>
  </section>
<?php if (!empty($member['is_admin']) && (string)$member['is_admin'] === '1'): ?>
  <section class="card">
    <h2>관리자 포인트 충전</h2>
    <form method="post" action="point.php">
      <input type="hidden" name="csrf_token" value="<?=$csrf?>">
      <input type="hidden" name="action" value="admin_charge">
      <label for="charge-target">대상 회원 아이디</label>
      <input id="charge-target" name="target_user_id" maxlength="80" required>
      <label for="charge-amount">충전 포인트</label>
      <input id="charge-amount" name="amount" type="number" min="1" max="100000000" required>
      <label for="charge-memo">메모</label>
      <input id="charge-memo" name="memo" maxlength="255">
      <button type="submit">회원 포인트 충전</button>
    </form>
  </section>
  <section class="card">
    <h2>관리자 선물 보내기</h2>
    <form method="post" action="point.php">
      <input type="hidden" name="csrf_token" value="<?=$csrf?>">
      <input type="hidden" name="action" value="admin_gift">
      <label for="gift-target">받는 회원 아이디</label>
      <input id="gift-target" name="target_user_id" maxlength="80" required>
      <label for="gift-amount">선물 포인트</label>
      <input id="gift-amount" name="amount" type="number" min="1" max="100000000" required>
      <label for="gift-memo">메시지</label>
      <textarea id="gift-memo" name="memo" maxlength="255" rows="3"></textarea>
      <button class="secondary" type="submit">포인트 선물하기</button>
    </form>
  </section>
<?php endif; ?>
</main>
</body>
</html>
