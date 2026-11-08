<?php
require_once __DIR__ . '/zbpath.php';
require_once $_zb_path . '_head.php';
require_once __DIR__ . '/security.php';

if (!isset($connect)) {
    http_response_code(500);
    exit('Database connection is unavailable.');
}

$per_page = 20;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
    'options' => ['default' => 1, 'min_range' => 1],
]);
$page = max(1, min((int)$page, 100000));
$offset = ($page - 1) * $per_page;

$count_result = mysql_query(
    "SELECT COUNT(*) AS total FROM zetyx_member_table WHERE rankpoint > 0",
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

$list_sql = "SELECT no, user_id, name, nick_name, rankpoint, gamepoint,
                    allseven, allitemh, alliteml, crossseven
               FROM zetyx_member_table
              WHERE rankpoint > 0
              ORDER BY rankpoint DESC, allseven DESC, allitemh DESC,
                       alliteml DESC, crossseven DESC, no ASC
              LIMIT $offset, $per_page";
$list_result = mysql_query($list_sql, $connect);
if (!$list_result) {
    slot_fail_database();
}

function ranking_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ranking_display_name(array $row): string
{
    $name = trim((string)($row['nick_name'] ?? ''));
    if ($name === '') {
        $name = trim((string)($row['name'] ?? ''));
    }
    if ($name === '') {
        $name = '회원';
    }
    return $name;
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>슬롯 전체랭킹</title>
<style>
:root { color-scheme: light; font-family: Arial, sans-serif; }
body { margin: 0; background: #f6f7fb; color: #1f2937; }
.ranking-wrap { width: min(960px, calc(100% - 32px)); margin: 32px auto; }
.ranking-title { display: flex; justify-content: space-between; align-items: end; gap: 16px; margin-bottom: 18px; }
.ranking-title h1 { margin: 0; font-size: 28px; }
.ranking-title p { margin: 0; color: #6b7280; font-size: 13px; }
.ranking-card { overflow: hidden; background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; box-shadow: 0 8px 24px rgba(17, 24, 39, .06); }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 14px 12px; border-bottom: 1px solid #eef0f4; text-align: right; white-space: nowrap; }
th { background: #f9fafb; color: #6b7280; font-size: 12px; }
th:first-child, td:first-child, th:nth-child(2), td:nth-child(2) { text-align: center; }
td { font-size: 14px; }
tbody tr:last-child td { border-bottom: 0; }
tbody tr:nth-child(-n+3) td:first-child { font-weight: 700; color: #b45309; }
.member-name { text-align: left !important; max-width: 180px; overflow: hidden; text-overflow: ellipsis; }
.empty { padding: 48px 20px; text-align: center; color: #6b7280; }
.pagination { display: flex; justify-content: center; gap: 6px; padding: 18px; }
.pagination a, .pagination strong { min-width: 32px; padding: 8px 10px; border-radius: 8px; text-align: center; text-decoration: none; font-size: 13px; }
.pagination a { color: #374151; background: #f3f4f6; }
.pagination strong { color: #fff; background: #7c3aed; }
@media (max-width: 680px) {
  .ranking-wrap { width: calc(100% - 20px); margin: 16px auto; }
  .ranking-title { display: block; }
  .ranking-title p { margin-top: 6px; }
  th, td { padding: 11px 7px; font-size: 12px; }
  th:nth-child(5), td:nth-child(5), th:nth-child(6), td:nth-child(6) { display: none; }
}
</style>
</head>
<body>
<main class="ranking-wrap">
  <header class="ranking-title">
    <div>
      <h1>슬롯 전체랭킹</h1>
      <p>랭킹포인트가 있는 회원을 기준으로 집계합니다. 총 <?=ranking_escape($total_rows)?>명</p>
    </div>
  </header>
  <section class="ranking-card" aria-label="슬롯 전체랭킹 목록">
<?php if ($total_rows === 0): ?>
    <div class="empty">아직 등록된 랭킹 기록이 없습니다.</div>
<?php else: ?>
    <table>
      <thead>
        <tr>
          <th scope="col">순위</th>
          <th scope="col" class="member-name">회원</th>
          <th scope="col">랭킹포인트</th>
          <th scope="col">올세븐</th>
          <th scope="col">고급 아이템</th>
          <th scope="col">일반 아이템</th>
          <th scope="col">크로스세븐</th>
        </tr>
      </thead>
      <tbody>
<?php $rank = $offset + 1; while ($row = mysql_fetch_assoc($list_result)): ?>
        <tr>
          <td><?=ranking_escape($rank++)?></td>
          <td class="member-name"><?=ranking_escape(ranking_display_name($row))?></td>
          <td><?=ranking_escape(number_format((int)$row['rankpoint']))?></td>
          <td><?=ranking_escape((int)$row['allseven'])?></td>
          <td><?=ranking_escape((int)$row['allitemh'])?></td>
          <td><?=ranking_escape((int)$row['alliteml'])?></td>
          <td><?=ranking_escape((int)$row['crossseven'])?></td>
        </tr>
<?php endwhile; ?>
      </tbody>
    </table>
    <nav class="pagination" aria-label="랭킹 페이지 이동">
<?php for ($p = max(1, $page - 3); $p <= min($total_pages, $page + 3); $p++): ?>
<?php if ($p === $page): ?>
      <strong aria-current="page"><?=ranking_escape($p)?></strong>
<?php else: ?>
      <a href="?page=<?=ranking_escape($p)?>"><?=ranking_escape($p)?></a>
<?php endif; ?>
<?php endfor; ?>
    </nav>
<?php endif; ?>
  </section>
</main>
</body>
</html>
