<?php
require_once __DIR__ . "/security.php";
slot_require_login();
$slot_user_id = slot_user_id();
/******* 확률 및 그림 번호 *****
0.수박 1.체리 2.오렌지 3.망고 4.1바 5.세븐 6.s종 7.백종 8.2바 9.3바

오렌지90  망고80  s종60  수박60  1바50  백종50  체리40  2바30  3바25  세븐15

*********************************/

$temp=0;

$number[0]=0;

$number[1]=0;

$number[2]=0;

$number[3]=0;

$number[4]=0;

$number[5]=0;

$number[6]=0;

$number[7]=0;

$number[8]=0;

$number[9]=0;



$jackpot=slot_game_jackpot();

$cross=random_int(2000, 2004);

$allrand=random_int(3000, 3002);

$allmode=100;		// 올그림 여부

$crossmode=0;		// 세븐 서비스 모드

// 더블배당 찬스
$doublepoint=slot_game_double_multiplier();


/* 올그림 확률 생성 */
$all=random_int(0, 500);

	if($all>0&&$all<=90)
	{
		$allimage=2;
	}
	else if($all>90&&$all<=170)
	{
		$allimage=3;

	}
	else if($all>170&&$all<=230)
	{
		$allimage=1;

	}
	else if($all>230&&$all<=290)
	{
		$allimage=7;

	}
	else if($all>290&&$all<=340)
	{
		$allimage=0;

	}
	else if($all>340&&$all<=390)
	{
		$allimage=6;

	}
	else if($all>390&&$all<=430)
	{
		$allimage=4;

	}
	else if($all>430&&$all<=460)
	{
		$allimage=8;

	}
	else if($all>460&&$all<=485)
	{
		$allimage=9;

	}
	else if($all>485&&$all<=500)
	{
		$allimage=5;
		$crossmode=1;

	}

/* 기본 번호 생성 */

for($i=1;$i<10;$i++)
{
	$temp=random_int(1, 500);

	if($temp>0&&$temp<=60)
		$num [$i] =2;
	else if($temp>60&&$temp<=110)
		$num [$i] =3;
	else if($temp>110&&$temp<=140)
		$num [$i] =1;
	else if($temp>140&&$temp<=240)
		$num [$i] =7;
	else if($temp>240&&$temp<=280)
		$num [$i] =0;
	else if($temp>280&&$temp<=380)
		$num [$i] =6;
	else if($temp>380&&$temp<=430)
		$num [$i] =4;
	else if($temp>430&&$temp<=460)
		$num [$i] =8;
	else if($temp>460&&$temp<=485)
		$num [$i] =9;
	else if($temp>485&&$temp<=500)
		$num [$i] =5;

}

$lines=0;		/* 같은그림 몇줄인지 저장 */
$total_points=0;	/* 총 시상에 더해질 점수 */
$seven=0;		/* 막세븐 갯수 */
$bars=0;
$fruits=0;		/* 올과조건 */
$allbell=0;		/* 올잡종 */
$allbar=0;		/* 올잡바 */
$bells=0;
$bellcount=0;

slot_db_exec('UPDATE zetyx_member_table SET bellbonus = bellbonus - 1 WHERE user_id = :user_id AND bellbonus > 0', [':user_id' => $slot_user_id]);
?>