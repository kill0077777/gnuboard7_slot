<?php
require_once __DIR__ . '/zbpath.php';
require_once $_zb_path . '_head.php';
require_once __DIR__ . '/security.php';
slot_require_login();
if (empty($member['is_admin']) || (string)$member['is_admin'] !== '1') {
    http_response_code(403);
    exit('Admin only.');
}

$action = (string)($_POST['action'] ?? '');
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    slot_require_csrf();
    try {
        if ($action === 'install') {
            foreach (['install.sql', 'ranking.sql', 'event.sql', 'point.sql', 'slot_settings.sql'] as $file) {
                slot_db_exec_script(__DIR__ . '/' . $file);
            }
            $message = '슬롯·랭킹·이벤트·포인트 테이블 설치가 완료되었습니다.';
        } elseif ($action === 'uninstall') {
            slot_db_exec_script(__DIR__ . '/uninstall.sql');
            slot_db()->exec('DROP TABLE IF EXISTS slot_point_ledger, slot_daily_point_claims, slot_events, slot_game_settings');
            $message = '슬롯 확장 테이블 삭제가 완료되었습니다.';
        } else {
            $error = '지원하지 않는 작업입니다.';
        }
    } catch (Throwable $e) {
        error_log('slot install operation failed: ' . $e->getMessage());
        $error = 'DB 작업에 실패했습니다. 서버 로그를 확인하세요.';
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>슬롯 DB 설치</title>
<style>
body { margin: 0; background: #f8fafc; color: #1f2937; font-family: Arial, sans-serif; }
.wrap { width: min(520px, calc(100% - 32px)); margin: 48px auto; }
.card { padding: 24px; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 8px 24px rgba(15,23,42,.06); }
.notice, .error { margin-bottom: 16px; padding: 12px; border-radius: 8px; }
.notice { background: #ecfdf5; color: #166534; }
.error { background: #fef2f2; color: #991b1b; }
button { margin: 6px 6px 0 0; padding: 10px 16px; border: 0; border-radius: 8px; color: #fff; background: #7c3aed; cursor: pointer; font-weight: 700; }
button.danger { background: #b91c1c; }
small { color: #64748b; }
</style>
</head>
<body>
<main class="wrap">
  <section class="card">
    <h1>슬롯 DB 관리</h1>
<?php if ($message !== ''): ?><div class="notice"><?=htmlspecialchars($message, ENT_QUOTES, 'UTF-8')?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="error"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></div><?php endif; ?>
    <p><small>그누보드7 운영 서버에서는 설치가 끝난 뒤 이 파일의 웹 접근을 차단하거나 삭제하세요.</small></p>
    <form method="post" action="install.php" onsubmit="return confirm(this.dataset.action === 'uninstall' ? '정말 삭제하시겠습니까?' : '설치하시겠습니까?');">
      <input type="hidden" name="csrf_token" value="<?=htmlspecialchars(slot_csrf_token(), ENT_QUOTES, 'UTF-8')?>">
      <input type="hidden" name="action" value="">
      <button type="submit" class="install" data-action="install">설치하기</button>
      <button type="submit" class="danger" data-action="uninstall">삭제하기</button>
    </form>
  </section>
</main>
<script>
document.querySelectorAll('button[data-action]').forEach(function (button) {
  button.addEventListener('click', function () { this.form.elements.action.value = this.dataset.action; this.form.dataset.action = this.dataset.action; });
});
</script>
</body>
</html>
