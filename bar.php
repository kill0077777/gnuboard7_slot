<?php
require_once __DIR__ . "/security.php";
slot_require_login();
$slot_user_id = slot_user_id();
/******* 확률 및 그림 번호 *****
0.수박 1.체리 2.오렌지 3.망고 4.1바 5.세븐 6.s종 7.백종 8.2바 9.3바

오렌지90  망고80  s종60  수박60  1바50  백종50  체리40  2바30  3바25  세븐15
*********************************/

// 항목 초기화
$temp=0;
$num=array(8);
$gamemsg="";
$allmode=100;		// 올그림 여부

// 잭팟점수 생성
$jackpot=random_int(3000, 18000);

// 십자세븐 확률
$cross=20000;

// 올과일 확률
$allrand=20000;

/* 올그림 확률 생성 */
$all=20000;

// 더블배당 찬스
$double=random_int(0, 80);

$doublepoint=1;

if($double<1) $doublepoint=5;
else if($double<2) $doublepoint=3;
else if($double<4) $doublepoint=2;
else $doublepoint=1;

/* 기본 번호 생성 */
for($i=1;$i<10;$i++)
{
	$temp=random_int(1, 500);

	if($temp>0&&$temp<=150)
	{
		$num [$i] =4;
	}
	else if($temp>150&&$temp<=350)
	{
		$num [$i] =10;
	}
	else if($temp>350&&$temp<=430)
	{
		$num [$i] =8;
	}
	else if($temp>430&&$temp<=500)
	{
		$num [$i] =9;
	}
}
$total_points=0;	/* 총 시상에 더해질 점수 */
$seven=0;		/* 막세븐 갯수 */
$bars=0;
$fruits=0;		/* 올과조건 */
$allbell=0;		/* 올잡종 */
$allbar=0;		/* 올잡바 */
$bells=0;

slot_db_exec('UPDATE zetyx_member_table SET bar = bar - 1 WHERE user_id = :user_id AND bar > 0', [':user_id' => $slot_user_id]);
?>