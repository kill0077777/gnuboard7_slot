<?php
require_once __DIR__ . '/zbpath.php';
require_once $_zb_path . '_head.php';
require_once __DIR__ . '/security.php';

if (!isset($connect)) {
    http_response_code(500);
    exit('Database connection is unavailable.');
}

function event_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function event_safe_image_path(string $path): string
{
    $path = trim($path);
    if ($path === '' || !preg_match('~^/?(?:images|uploads)/[A-Za-z0-9._/-]+$~', $path)) {
        return '';
    }
    return $path;
}

function event_status(string $start_at, string $end_at, string $now): array
{
    if ($now < $start_at) {
        return ['예정', 'upcoming'];
    }
    if ($now > $end_at) {
        return ['종료', 'ended'];
    }
    return ['진행중', 'ongoing'];
}

$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['default' => 0, 'min_range' => 0],
]);
$event_id = max(0, (int)$event_id);
$now = date('Y-m-d H:i:s');

if ($event_id > 0) {
    $detail_sql = "SELECT event_id, title, content, image_path, start_at, end_at, created_at
                     FROM slot_events
                    WHERE event_id = $event_id
                      AND is_visible = 1
                    LIMIT 1";
    $detail_result = mysql_query($detail_sql, $connect);
    if (!$detail_result) {
        slot_fail_database();
    }
    $event = mysql_fetch_assoc($detail_result);
    if (!$event) {
        http_response_code(404);
        exit('Event not found.');
    }
    [$status_label, $status_class] = event_status($event['start_at'], $event['end_at'], $now);
    $event_image = event_safe_image_path((string)$event['image_path']);
} else {
    $per_page = 10;
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
        'options' => ['default' => 1, 'min_range' => 1],
    ]);
    $page = max(1, min((int)$page, 100000));
    $count_result = mysql_query(
        "SELECT COUNT(*) AS total FROM slot_events WHERE is_visible = 1",
        $connect
    );
    if (!$count_result) {
        slot_fail_database();
    }
    $count_row = mysql_fetch_assoc($count_result);
    $total_rows = (int)$count_row['total'];
    $total_pages = max(1, (int)ceil($total_rows / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $list_sql = "SELECT event_id, title, content, image_path, start_at, end_at, created_at
                   FROM slot_events
                  WHERE is_visible = 1
                  ORDER BY start_at DESC, event_id DESC
                  LIMIT $offset, $per_page";
    $list_result = mysql_query($list_sql, $connect);
    if (!$list_result) {
        slot_fail_database();
    }
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $event_id > 0 ? event_escape($event['title']) : '이벤트게시판' ?></title>
<style>
:root { font-family: Arial, sans-serif; color: #1f2937; }
body { margin: 0; background: #f8fafc; }
.event-wrap { width: min(960px, calc(100% - 32px)); margin: 32px auto; }
h1 { margin: 0 0 18px; font-size: 28px; }
.event-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 24px rgba(15,23,42,.06); }
.event-list { display: grid; gap: 0; }
.event-item { display: block; padding: 20px; color: inherit; text-decoration: none; border-bottom: 1px solid #eef0f4; }
.event-item:last-child { border-bottom: 0; }
.event-item:hover { background: #faf5ff; }
.event-item h2 { margin: 0 0 9px; font-size: 18px; }
.event-meta { display: flex; flex-wrap: wrap; gap: 8px 14px; color: #6b7280; font-size: 13px; }
.badge { display: inline-block; padding: 3px 8px; border-radius: 99px; font-size: 12px; font-weight: 700; }
.badge.ongoing { color: #166534; background: #dcfce7; }
.badge.upcoming { color: #1d4ed8; background: #dbeafe; }
.badge.ended { color: #6b7280; background: #f3f4f6; }
.event-content { padding: 26px; line-height: 1.75; white-space: pre-wrap; word-break: break-word; }
.event-content img { max-width: 100%; height: auto; }
.event-image { display: block; max-width: 100%; max-height: 360px; margin: 0 auto 20px; object-fit: contain; }
.event-back { display: inline-block; margin-bottom: 14px; color: #6d28d9; text-decoration: none; font-size: 14px; }
.event-title { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 24px 26px 0; }
.event-title h1 { margin: 0; }
.empty { padding: 54px 20px; text-align: center; color: #6b7280; }
.pagination { display: flex; justify-content: center; gap: 6px; padding: 18px; }
.pagination a, .pagination strong { min-width: 30px; padding: 8px 10px; border-radius: 8px; text-align: center; text-decoration: none; font-size: 13px; }
.pagination a { color: #374151; background: #f3f4f6; }
.pagination strong { color: #fff; background: #7c3aed; }
</style>
</head>
<body>
<main class="event-wrap">
<?php if ($event_id > 0): ?>
  <a class="event-back" href="event.php">← 이벤트 목록</a>
  <article class="event-card">
    <header class="event-title">
      <h1><?=event_escape($event['title'])?></h1>
      <span class="badge <?=event_escape($status_class)?>"><?=event_escape($status_label)?></span>
    </header>
    <div class="event-content">
<?php if ($event_image !== ''): ?>
      <img class="event-image" src="<?=event_escape($event_image)?>" alt="<?=event_escape($event['title'])?>">
<?php endif; ?>
      <div><?=event_escape($event['content'])?></div>
      <p class="event-meta">기간: <?=event_escape($event['start_at'])?> ~ <?=event_escape($event['end_at'])?></p>
    </div>
  </article>
<?php else: ?>
  <h1>이벤트게시판</h1>
  <section class="event-card" aria-label="이벤트 목록">
<?php if ($total_rows === 0): ?>
    <div class="empty">등록된 이벤트가 없습니다.</div>
<?php else: ?>
    <div class="event-list">
<?php while ($row = mysql_fetch_assoc($list_result)): [$label, $class] = event_status($row['start_at'], $row['end_at'], $now); ?>
      <a class="event-item" href="event.php?id=<?=event_escape((int)$row['event_id'])?>">
        <h2><?=event_escape($row['title'])?></h2>
        <div class="event-meta">
          <span class="badge <?=event_escape($class)?>"><?=event_escape($label)?></span>
          <span><?=event_escape($row['start_at'])?> ~ <?=event_escape($row['end_at'])?></span>
        </div>
      </a>
<?php endwhile; ?>
    </div>
    <nav class="pagination" aria-label="이벤트 페이지 이동">
<?php for ($p = max(1, $page - 3); $p <= min($total_pages, $page + 3); $p++): ?>
<?php if ($p === $page): ?><strong aria-current="page"><?=event_escape($p)?></strong>
<?php else: ?><a href="?page=<?=event_escape($p)?>"><?=event_escape($p)?></a>
<?php endif; ?>
<?php endfor; ?>
    </nav>
<?php endif; ?>
  </section>
<?php endif; ?>
</main>
</body>
</html>
