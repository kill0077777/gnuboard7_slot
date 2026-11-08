-- 슬롯 전체랭킹 조회용 인덱스
-- 이미 같은 인덱스가 있으면 중복 실행하지 마세요.
ALTER TABLE `zetyx_member_table`
  ADD INDEX `idx_slot_full_ranking`
  (`rankpoint`, `allseven`, `allitemh`, `alliteml`, `crossseven`, `no`);
