<?php
require_once __DIR__ . "/zbpath.php";
require_once $_zb_path . "_head.php";
require_once __DIR__ . "/security.php";
slot_require_login();
slot_require_csrf();
$slot_user_id = slot_user_id();
$bet_max = slot_setting_int('bet_max', 300, 20, 100000);
$bet_min = slot_setting_int('bet_min', 20, 1, $bet_max);
$betmoney = slot_int_input('betmoney', 0, $bet_max, 0);
$h1 = slot_bool_input("h1");
$h2 = slot_bool_input("h2");
$h3 = slot_bool_input("h3");
$holds = ($h1 ? 1 : 0) + ($h2 ? 1 : 0) + ($h3 ? 1 : 0);

$reg_date=time();	 // 현재의 시간구함
$mode=0;			 // 서비스 모드
$slotclr="#ffffff";	 // 배경색
$rankdb=0;			 // 랭킹 업데이트 여부
//$betmoney=100;	 // 배팅머니
$title="기타시상";	 // 시상명

// 슬롯 정보용 잭팟변수
$all7=0;
$allh=0;
$alll=0;
$cro7=0;

// 슬롯 회원 정보
$sdata = slot_db_one('SELECT * FROM zetyx_member_table WHERE user_id = :user_id LIMIT 1', [':user_id' => $slot_user_id]);
if ($sdata === null) { slot_fail_database(); }

if($member['no']&&$sdata['bar']>0) // 디비에 남은 바 서비스가 존재할 경우
{
	include "bar.php";
	$mode=1;
	$slotclr="#CCF6FD";
	echo "<script>top.setBetFalse();</script>";
}
else if($member['no']&&$sdata['bellbonus']>0) // 디비에 남은 종 서비스가 존재할 경우
{
	include "bell.php";
	$mode=2;
	$slotclr="#CCF6FD";
	echo "<script>top.setBetFalse();</script>";
}
else
{
	include "normal.php";
	$mode=0;
	$slotclr="#ffffff";
	echo "<script>top.setBetTrue();</script>";
}

if($doublepoint==5)		$slotclr="#FFBBBB";		// 슬롯 5배당 배경색
else if($doublepoint==3)	$slotclr="#BBBBFF";	// 슬롯 3배당 배경색
else if($doublepoint==2)	$slotclr="#BBFFBB";	// 슬롯 2배당 배경색

$img[1]="images/spin".$num[1].".gif";
$img[2]="images/spin".$num[2].".gif";
$img[3]="images/spin".$num[3].".gif";
$img[4]="images/spin".$num[4].".gif";
$img[5]="images/spin".$num[5].".gif";
$img[6]="images/spin".$num[6].".gif";
$img[7]="images/spin".$num[7].".gif";
$img[8]="images/spin".$num[8].".gif";
$img[9]="images/spin".$num[9].".gif";

if($member['no']&&$sdata['gamepoint']>=$betmoney&&$betmoney>=$bet_min) {
$debit_count = slot_db_exec(
    'UPDATE zetyx_member_table SET gamepoint = gamepoint - :betmoney
      WHERE user_id = :user_id AND gamepoint >= :minimum',
    [':betmoney' => $betmoney, ':user_id' => $slot_user_id, ':minimum' => $betmoney]
);
if ($debit_count !== 1) {
    http_response_code(409);
    exit("Insufficient points or duplicate request.");
}

if($holds>0)
{
	if($h1)
	{
		$num[1]=$sdata['box1'];	$img[1]="images/stop".$num[1].".gif";
		$num[4]=$sdata['box4'];	$img[4]="images/stop".$num[4].".gif";
		$num[7]=$sdata['box7'];	$img[7]="images/stop".$num[7].".gif";
	}
	if($h2)
	{
		$num[2]=$sdata['box2'];	$img[2]="images/stop".$num[2].".gif";
		$num[5]=$sdata['box5'];	$img[5]="images/stop".$num[5].".gif";
		$num[8]=$sdata['box8'];	$img[8]="images/stop".$num[8].".gif";
	}
	if($h3)
	{
		$num[3]=$sdata['box3'];	$img[3]="images/stop".$num[3].".gif";
		$num[6]=$sdata['box6'];	$img[6]="images/stop".$num[6].".gif";
		$num[9]=$sdata['box9'];	$img[9]="images/stop".$num[9].".gif";
	}
	$allmode=100;
}



// 한줄 검사
function lineCheck($item,$linenum)
{
	global $bars;
	global $bells;
	global $gamemsg;
	global $mode;
	global $betmoney;
	
	$temp_point=0;

	if($item==1)			// 체리
		$temp_point=$betmoney;
	else if($item==2)		// 오렌지
		$temp_point=($betmoney*1.2);
	else if($item==3)		// 망과
		$temp_point=($betmoney*1.5);
	else if($item==4)		// 원바
	{
		$temp_point=($betmoney*2.5);
		if($mode!=1)$bars=$bars+3;
		echo "<script>top.setBetFalse;</script>";
	}
	else if($item==5)	    // 세븐
		$temp_point=($betmoney*20);

	else if($item==6)		// 슈퍼종
	{
		$bells=$bells+1;
	}
	else if($item==7)		// 백종
	{
		//	잡종 보너스로 대체
	}

	else if($item==8)		// 투바
	{
		$temp_point=($betmoney*5);
		if($mode!=1)$bars=$bars+4;
		echo "<script>top.setBetFalse;</script>";
	}
	else if($item==9)		// 쓰리바
	{
		$temp_point=($betmoney*12);
		if($mode!=1)$bars=$bars+5;
		echo "<script>top.setBetFalse;</script>";
	}
	else if($item==0)		// 수박
		$temp_point=($betmoney*2.5);
	else if($item==11)		// 잡바
		$temp_point=($betmoney*1.2);
	else if($item==12)		// 잡종
		$temp_point=($betmoney*2);
	
	if($item!=10)
	{	
		$gamemsg="Wins";
		echo "<script>lamp[$linenum-1]=1;</script>";
	}
	
	return $temp_point;
}

?>

<html>
<head>
<title>인디 슬롯 베타 v1.0</title>
<script language="JavaScript">
<!--
var lamp = new Array(8);
var slot = new Array(9);

var gamemsg="";
var idx=0;
var allfruit=0;
var sevens=0;

// 스핀 사운드
if(top.getSound())
	top.document.getElementById('effect_spin').play();

for(idx=0;idx<8;idx++)
{
	lamp[idx] = 0;
}
for(idx=0;idx<9;idx++)
{
	slot[idx] = 0;
}

function change(string) {

image_string = string;

string_length = image_string.length;

	while (string_length != 0){

	  place_space= image_string.indexOf(" ");

	  image_change_name = image_string.substring(5,place_space);

	  place_end = image_string.indexOf(")");

	  image_change_src = image_string.substring(place_space + 5,place_end);

	  object_image = top.document.getElementById(image_change_name);
	  if (!object_image) { continue; }

	  object_image.src=image_change_src;

	  string_lenght = image_string.length;

	  image_string = image_string.substring(place_end+1,string_lenght);

	  string_length = image_string.length;
	  }
}

// 화면 램프 체크
function slotOn(value){
	
	var i=parseInt(value);
	slot[i-1]=1;
}

function slotOnAll(){
	var i;
	for(i=0;i<9;i++)
	{
		if(slot[i]==1)
			top.document.getElementById("slot"+(i+1)).style.backgroundColor="#FFBBBB";
	}
}
function slotSetAll(){
	var i;
	for(i=0;i<9;i++)
	{
		top.document.getElementById("slot"+(i+1)).style.backgroundColor="<?=$slotclr?>";
	}
}

// 라인 램프
function lampOn(value){

	var temp = parseInt(value);
	top.document.getElementById("lamp"+(temp+1)).style.backgroundColor="#FF0000";
	switch(temp)
	{
		case 0:
			slotOn(4);slotOn(5);slotOn(6);
			break;
		case 1:
			slotOn(1);slotOn(2);slotOn(3);
			break;
		case 2:
			slotOn(7);slotOn(8);slotOn(9);
			break;
		case 3:
			slotOn(1);slotOn(5);slotOn(9);
			break;
		case 4:
			slotOn(3);slotOn(5);slotOn(7);
			break;
		case 5:
			slotOn(1);slotOn(4);slotOn(7);
			break;
		case 6:
			slotOn(2);slotOn(5);slotOn(8);
			break;
		case 7:
			slotOn(3);slotOn(6);slotOn(9);
			break;

	}
}
function lampOff(){
	var i;
	for(i=1;i<9;i++)
	{
		top.document.getElementById("lamp"+i).style.backgroundColor="#FFDDDD";
	}
}

function showHold(){
	top.document.getElementById("holdtext1").style.visibility="visible";
	top.document.getElementById("holdtext2").style.visibility="visible";
	top.document.getElementById("holdtext3").style.visibility="visible";
}

function hideHold(){
	top.document.getElementById("holdtext1").style.visibility="hidden";
	top.document.getElementById("holdtext2").style.visibility="hidden";
	top.document.getElementById("holdtext3").style.visibility="hidden";
}

function setting(value){

	var bonus='<?=$bars?>';
	var totalbonus=parseInt(value);
	var gap=1000;
	
	

	if(<?=$h1?>==false)
	{
		setTimeout("top.document.getElementById('i1').src='images/<?=$num[1]?>.gif'",gap);
		setTimeout("top.document.getElementById('i4').src='images/<?=$num[4]?>.gif'",gap+=200);
		setTimeout("top.document.getElementById('i7').src='images/<?=$num[7]?>.gif'",gap+=200);
		gap+=400;
	}

	if(<?=$h2?>==false)
	{
		setTimeout("top.document.getElementById('i2').src='images/<?=$num[2]?>.gif'",gap);
		setTimeout("top.document.getElementById('i5').src='images/<?=$num[5]?>.gif'",gap+=200);
		setTimeout("top.document.getElementById('i8').src='images/<?=$num[8]?>.gif'",gap+=200);
		gap+=400;
	}
	if(<?=$h3?>==false)
	{
		setTimeout("top.document.getElementById('i3').src='images/<?=$num[3]?>.gif'",gap);
		setTimeout("top.document.getElementById('i6').src='images/<?=$num[6]?>.gif'",gap+=200);
		setTimeout("top.document.getElementById('i9').src='images/<?=$num[9]?>.gif'",gap+=200);
		gap+=400;
	}	
	
	var i=0;

	for(i=0;i<8;i++)
	{
		if(lamp[i]==1)
			setTimeout("lampOn("+i+")",gap+400);

	}
	setTimeout("slotOnAll()",gap+400);
	
	setTimeout("top.document.getElementById('bar').value=gamemsg",gap+400);

	if(allfruit==9)
	{
		setTimeout("top.MM_showHideLayers('layer1','','hide','layer2','','show');",gap+=800);
		setTimeout("top.document.getElementById('snd').src='swf/fruits.html'",gap+=200);
		setTimeout("top.MM_showHideLayers('layer2','','hide','layer1','','show');",gap+=6000);
		setTimeout("top.setPlay()",gap);
		setTimeout("top.setHoldFalse()",gap);
	}
	else if(sevens>=5)
	{
		setTimeout("top.MM_showHideLayers('layer1','','hide','layer2','','show');",gap+=800);
		setTimeout("top.document.getElementById('snd').src='swf/seven.html'",gap+=200);
		setTimeout("top.MM_showHideLayers('layer2','','hide','layer1','','show');",gap+=10800);
		setTimeout("top.setPlay()",gap);
		setTimeout("top.setHoldFalse()",gap);
	}
	else if(gamemsg!="Game Over")
	{
		if((totalbonus < <?=$betmoney?>) && top.getSound())
			setTimeout("top.document.getElementById('effect_swin').play();",gap+=300);
		else if((totalbonus < <?=$betmoney*3?>) && top.getSound())
			setTimeout("top.document.getElementById('effect_mwin').play();",gap+=300);
		else if(totalbonus >=<?=$betmoney*100?>)
		{
			setTimeout("top.document.getElementById('effect_jp').movie='#';",gap+=300);
			setTimeout("top.document.getElementById('effect_jp').movie='swf/jackpot.swf';",gap);
			setTimeout("top.document.getElementById('effect_jp').loop=false;",gap+=5000);
		}
		else if(top.getSound())
				setTimeout("top.document.getElementById('effect_bwin').play();",gap+=500);
		
		setTimeout("top.setHoldFalse()",gap);
		setTimeout("top.setPlay()",gap+=500);
		
	}
	else
	{
		if(top.getSound())
			setTimeout("top.document.getElementById('effect_over').play();",gap+=300);
		
		setTimeout("top.setPlay()",gap+=500);

		if(<?=$holds?>>0 || <?=$mode?>!=0)
			setTimeout("top.setHoldFalse()",gap);
		else
		{
			setTimeout("top.setHoldTrue()",gap);
			setTimeout("showHold()",gap);
		}
	}
	
	if(top.getAuto()==true)
		setTimeout("top.replay()",gap+300);
	else
		setTimeout("top.focus()",gap+300);
}
//-->
</script>
<?php
// 게임 스크립트 시작! 

// 홀드 클리어
echo "<script>top.holdClear();</script>";
echo "<script>hideHold();</script>";

// 슬롯 설정
echo "<script>slotSetAll();</script>";

// 램프끄기
echo "<script>lampOff();</script>";

// 돌아가는 이미지 초기화
for($i=1;$i<10;$i++)
{
	echo "<script>top.document.getElementById('i".$i."').src='".$img[$i]."';</script>";
}

// 게임머니 표시
echo "<script>top.document.getElementById('moneytxt').value='".number_format($sdata['gamepoint'])."';</script>";

// 잭팟 표시
echo "<script>top.document.getElementById('jackpottxt').value='$ ".number_format($jackpot)."';</script>";

// 슈퍼종 표시
echo "<script>top.document.getElementById('belltxt').value='".$sdata['bell']."';</script>";

// 서비스 표시
if($mode==1) echo "<script>top.document.getElementById('servicetxt').value='".$sdata['bar']."';</script>";
else if($mode==2) echo "<script>top.document.getElementById('servicetxt').value='".$sdata['bellbonus']."';</script>";
else if($mode==0) echo "<script>top.document.getElementById('servicetxt').value='0';</script>";
		

// 메세지 초기화
echo "<script>top.document.getElementById('bar').value='';</script>";

/* 시상 포인트 */
if($mode==2)
{
	for ($i=1;$i<10;$i++)
	{
		if($num[$i]==6||$num[$i]==7)
		{
			$bellcount=$bellcount+1;
			echo "<script>slotOn(".$i.");</script>";
		}
	}
	
	$total_points=$total_points+($bellcount*$betmoney);
	
}

/* 막세븐 */
for ($i=1;$i<10;$i++)
{
	if($num[$i]==5)
	{
		$seven=$seven+1;
	}
}
if($seven>1)
{
	for ($i=1;$i<10;$i++)
	{
		if($num[$i]==5)
		{
			echo "<script>slotOn(".$i.");</script>";
		}
	}
}


/*올잡바 조건*/
for ($i=1;$i<10;$i++)
{
	if($num[$i]==4||($num[$i]>7&&$num[$i]<10))
	$allbar=$allbar+1;
}

/*올잡종 조건*/
for ($i=1;$i<10;$i++)
{
	if($num[$i]==6||$num[$i]==7)
	$allbell=$allbell+1;
}

if($allmode==100)	// 올그림이 아니면
{
// 체리 한개

 if($num[1]==1)	$total_points=$total_points+($betmoney*3/5);
 if($num[2]==1)	$total_points=$total_points+($betmoney/5);
 if($num[3]==1)	$total_points=$total_points+($betmoney/5);
 if($num[4]==1)	$total_points=$total_points+($betmoney/5);
 if($num[7]==1) $total_points=$total_points+($betmoney*2/5);

// 체리 두개

 if($num[4]==$num[5]&&$num[5]==1) $total_points=$total_points+($betmoney/5); //1라인
 if($num[1]==$num[2]&&$num[2]==1) $total_points=$total_points+($betmoney/5); //2라인
 if($num[7]==$num[8]&&$num[8]==1) $total_points=$total_points+($betmoney/5); //3라인
 if($num[1]==$num[4]&&$num[4]==1) $total_points=$total_points+($betmoney/5); //4라인
 if($num[2]==$num[5]&&$num[5]==1) $total_points=$total_points+($betmoney/5); //5라인
 if($num[3]==$num[6]&&$num[6]==1) $total_points=$total_points+($betmoney/5); //6라인
 if($num[1]==$num[5]&&$num[5]==1) $total_points=$total_points+($betmoney/5); //7라인
 if($num[7]==$num[5]&&$num[5]==1) $total_points=$total_points+($betmoney/5); //8라인

/* 한줄 */
  if($num[4]==$num[5]&&$num[5]==$num[6]) $total_points+=lineCheck($num[4],1); //1라인
  if($num[1]==$num[2]&&$num[2]==$num[3]) $total_points+=lineCheck($num[1],2); //2라인
  if($num[7]==$num[8]&&$num[8]==$num[9]) $total_points+=lineCheck($num[7],3); //3라인
  if($num[1]==$num[5]&&$num[5]==$num[9]) $total_points+=lineCheck($num[1],4); //4라인
  if($num[3]==$num[5]&&$num[5]==$num[7]) $total_points+=lineCheck($num[3],5); //5라인
  if($num[1]==$num[4]&&$num[4]==$num[7]) $total_points+=lineCheck($num[1],6); //6라인
  if($num[2]==$num[5]&&$num[5]==$num[8]) $total_points+=lineCheck($num[2],7); //7라인
  if($num[3]==$num[6]&&$num[6]==$num[9]) $total_points+=lineCheck($num[3],8); //8라인

/*잡종한줄*/
if(($num[4]==6||$num[4]==7)&&($num[5]==6||$num[5]==7)&&($num[6]==6||$num[6]==7)) $total_points+=lineCheck(12,1); //1라인
if(($num[1]==6||$num[1]==7)&&($num[2]==6||$num[2]==7)&&($num[3]==6||$num[3]==7)) $total_points+=lineCheck(12,2); //2라인
if(($num[7]==6||$num[7]==7)&&($num[8]==6||$num[8]==7)&&($num[9]==6||$num[9]==7)) $total_points+=lineCheck(12,3); //3라인
if(($num[1]==6||$num[1]==7)&&($num[5]==6||$num[5]==7)&&($num[9]==6||$num[9]==7)) $total_points+=lineCheck(12,4); //4라인
if(($num[3]==6||$num[3]==7)&&($num[5]==6||$num[5]==7)&&($num[7]==6||$num[7]==7)) $total_points+=lineCheck(12,5); //5라인
if(($num[1]==6||$num[1]==7)&&($num[4]==6||$num[4]==7)&&($num[7]==6||$num[7]==7)) $total_points+=lineCheck(12,6); //6라인
if(($num[2]==6||$num[2]==7)&&($num[5]==6||$num[5]==7)&&($num[8]==6||$num[8]==7)) $total_points+=lineCheck(12,7); //7라인
if(($num[3]==6||$num[3]==7)&&($num[6]==6||$num[6]==7)&&($num[9]==6||$num[9]==7)) $total_points+=lineCheck(12,8); //8라인


/*잡바한줄*/
if(($num[4]==4||$num[4]==8||$num[4]==9)&&($num[5]==4||$num[5]==8||$num[5]==9)&&($num[6]==4||$num[6]==8||$num[6]==9)) $total_points+=lineCheck(11,1);
if(($num[1]==4||$num[1]==8||$num[1]==9)&&($num[2]==4||$num[2]==8||$num[2]==9)&&($num[3]==4||$num[3]==8||$num[3]==9)) $total_points+=lineCheck(11,2);
if(($num[7]==4||$num[7]==8||$num[7]==9)&&($num[8]==4||$num[8]==8||$num[8]==9)&&($num[9]==4||$num[9]==8||$num[9]==9)) $total_points+=lineCheck(11,3);
if(($num[1]==4||$num[1]==8||$num[1]==9)&&($num[5]==4||$num[5]==8||$num[5]==9)&&($num[9]==4||$num[9]==8||$num[9]==9)) $total_points+=lineCheck(11,4);
if(($num[3]==4||$num[3]==8||$num[3]==9)&&($num[5]==4||$num[5]==8||$num[5]==9)&&($num[7]==4||$num[7]==8||$num[7]==9)) $total_points+=lineCheck(11,5);
if(($num[1]==4||$num[1]==8||$num[1]==9)&&($num[4]==4||$num[4]==8||$num[4]==9)&&($num[7]==4||$num[7]==8||$num[7]==9)) $total_points+=lineCheck(11,6);
if(($num[2]==4||$num[2]==8||$num[2]==9)&&($num[5]==4||$num[5]==8||$num[5]==9)&&($num[8]==4||$num[8]==8||$num[8]==9)) $total_points+=lineCheck(11,7);
if(($num[3]==4||$num[3]==8||$num[3]==9)&&($num[6]==4||$num[6]==8||$num[6]==9)&&($num[9]==4||$num[9]==8||$num[9]==9)) $total_points+=lineCheck(11,8);
}

if($crossmode==0)
{
	if($seven==2) $total_points=$total_points+($betmoney*2);   //세븐2
	else if($seven==3) $total_points=$total_points+($betmoney*5);   //세븐3	
	else if($seven==4) $total_points=$total_points+($betmoney*20);  //세븐4
	else if($seven==5) $total_points=$total_points+($betmoney*60);  //세븐5
	else if($seven==6) $total_points=$total_points+($betmoney*100); //세븐6
	else if($seven==7) $total_points=$total_points+($betmoney*200); //세븐7
	else if($seven==8) $total_points=$total_points+($betmoney*400); //세븐8
}	

/* 다이아 */

if($num[2]==$num[4]&&$num[4]==$num[6]&&$num[6]==$num[8]&&$num[8]==1&&$num[8]!=$num[1]&&$num[8]!=$num[3]&&$num[8]!=$num[5]&&$num[8]!=$num[7]&&$num[8]!=$num[9]){
	$total_points=$total_points+5000;
	$title="체리 다이아";
}

if($num[2]==$num[4]&&$num[4]==$num[6]&&$num[6]==$num[8]&&$num[8]==5&&$num[8]!=$num[1]&&$num[8]!=$num[3]&&$num[8]!=$num[5]&&$num[8]!=$num[7]&&$num[8]!=$num[9]){
	$total_points=$total_points+5000;
	$title="세븐 다이아";
}


/* 사각 */

if($num[1]==$num[3]&&$num[3]==$num[7]&&$num[7]==$num[9]&&$num[9]==1&&$num[9]!=$num[2]&&$num[9]!=$num[4]&&$num[9]!=$num[5]&&$num[9]!=$num[6]&&$num[9]!=$num[8]){
       	$total_points=$total_points+5000;
		$title="체리 사각";
}

if($num[1]==$num[3]&&$num[3]==$num[7]&&$num[7]==$num[9]&&$num[9]==5&&$num[9]!=$num[2]&&$num[9]!=$num[4]&&$num[9]!=$num[5]&&$num[9]!=$num[6]&&$num[9]!=$num[8]){
       	$total_points=$total_points+5000;
		$title="세븐 사각";
}



/* 십자 */
if($num[2]==$num[4]&&$num[4]==$num[5]&&$num[5]==$num[6]&&$num[6]==$num[8]&&$num[8]==1){
	$total_points=$total_points+10000;
	$gamemsg="Bonus Wins";
	$title="체리 십자";
}
if($num[2]==$num[4]&&$num[4]==$num[5]&&$num[5]==$num[6]&&$num[6]==$num[8]&&$num[8]==5){
	$total_points=$total_points+($betmoney*40)+$jackpot;
	$gamemsg="Bonus Wins";
	$rankdb=4;
	$title="십자 세븐";
	echo "<script>sevens=5;</script>";
}

/* 엑스 */
if($num[1]==$num[3]&&$num[3]==$num[7]&&$num[7]==$num[9]&&$num[9]==$num[5]&&$num[5]==1){
	$total_points=$total_points+10000;
	$gamemsg="Bonus Wins";
	$title="체리 엑스";
}

if($num[1]==$num[3]&&$num[3]==$num[7]&&$num[7]==$num[9]&&$num[9]==$num[5]&&$num[5]==5){
	$total_points=$total_points+($betmoney*40);
	$gamemsg="Bonus Wins";
	$title="엑스 세븐";
	echo "<script>sevens=5;</script>";
}


/*올과조건*/
for ($i=1;$i<10;$i++)
{
	if($num[$i]<4 && $allmode==100)
	{
		$fruits=$fruits+1;
		echo "<script>allfruit=allfruit+1;</script>";
	}
}

/*올 과일 */
if($fruits==9)
{
	for ($i=1;$i<10;$i++)
	{
		echo "<script>slotOn(".$i.");</script>";
	}

	$total_points=$total_points+($betmoney*15);
	$gamemsg="Fluits";
	$title="올 과일";
}

/* 올 그림 */
if($allbar==9 && $allmode==100)
{
	$total_points=$total_points+($betmoney*60);
	$gamemsg="AnyBar";
	$rankdb=3;
	$title="올 잡바";
}

if($allbell==9 && $allmode==100)
{
	$total_points=$total_points+($betmoney*200);
	$gamemsg="AnyBell";
	$rankdb=3;
	$title="올 잡종";
}

if($allmode==1){
	$total_points=$total_points+($betmoney*320);   
	$gamemsg="Cherries";
	$rankdb=2;
	$title="올 체리";
}	

if($allmode==2){
	$total_points=$total_points+($betmoney*40);
	$gamemsg="Oranges";
	$rankdb=3;
	$title="올 오렌지";
}

if($allmode==3){
 	$total_points=$total_points+($betmoney*80);
	$gamemsg="Mangoes";
	$rankdb=3;
	$title="올 망과";
}

if($allmode==0){
	$total_points=$total_points+($betmoney*300);   
	$gamemsg="W.melons";
	$rankdb=3;
	$title="올 수박";
}	

if($allmode==4){
	$total_points=$total_points+($betmoney*150);
	$gamemsg="1Bars";
	$bars=$bars-24;
	$rankdb=3;
	$title="올 원바";
}

if($allmode==5){
	$total_points=$total_points+($betmoney*1000);
	$gamemsg="Jackpot Sevens";
	$rankdb=1;
	$title="올 세븐";
}

if($allmode==6){
	$total_points=$total_points+($betmoney*200);
	$gamemsg="S.bells";
	$rankdb=2;
	$title="올 슈퍼종";
}

if($allmode==7){
	$total_points=$total_points+($betmoney*200);
	$gamemsg="Bells";
	$rankdb=3;
	$title="올 백종";
}

if($allmode==8){
	$total_points=$total_points+($betmoney*300);   
	$gamemsg="2Bars";
	$bars=$bars-32;
	$rankdb=2;
	$title="올 투바";
}	
if($allmode==9){
	$total_points=$total_points+($betmoney*500);   
	$gamemsg="3Bars";
	$bars=$bars-40;
	$rankdb=2;
	$title="올 쓰리바";
}	

if($seven>=5)
	echo "<script>sevens=5;</script>";

if($bells>0)
{
	if($sdata['bell']<=$bells)
	{
	 slot_db_exec('UPDATE zetyx_member_table SET bellbonus = bellbonus + 5 WHERE user_id = :user_id', [':user_id' => $slot_user_id]);
	 slot_db_exec('UPDATE zetyx_member_table SET bell = bell - :bells_sub + 5 WHERE user_id = :user_id AND bell >= :bells_min', [':bells_sub' => $bells, ':user_id' => $slot_user_id, ':bells_min' => $bells]);
	 echo "<script>top.setBetFalse();</script>";
	}
	else
	{
	 slot_db_exec('UPDATE zetyx_member_table SET bell = bell - :bells_sub WHERE user_id = :user_id AND bell >= :bells_min', [':bells_sub' => $bells, ':user_id' => $slot_user_id, ':bells_min' => $bells]);
	}
}
if($mode!=1 && $bars>0)
{
	slot_db_exec('UPDATE zetyx_member_table SET bar = bar + :bars WHERE user_id = :user_id', [':bars' => $bars, ':user_id' => $slot_user_id]);
	echo "<script>top.setBetFalse();</script>";
}
	
switch($rankdb)
{
	case 1:
		slot_db_exec('UPDATE zetyx_member_table SET allseven = allseven + 1, rankpoint = rankpoint + 5 WHERE user_id = :user_id', [':user_id' => $slot_user_id]);
		echo "<script>top.setRecord(".($sdata['allseven']+1).",".$sdata['allitemh'].",".$sdata['alliteml'].",".$sdata['crossseven'].");</script>";
		$all7=1;
		break;
	case 2:
		slot_db_exec('UPDATE zetyx_member_table SET allitemh = allitemh + 1, rankpoint = rankpoint + 3 WHERE user_id = :user_id', [':user_id' => $slot_user_id]);
		echo "<script>top.setRecord(".$sdata['allseven'].",".($sdata['allitemh']+1).",".$sdata['alliteml'].",".$sdata['crossseven'].");</script>";
		$allh=1;
		break;
	case 3:
		slot_db_exec('UPDATE zetyx_member_table SET alliteml = alliteml + 1, rankpoint = rankpoint + 1 WHERE user_id = :user_id', [':user_id' => $slot_user_id]);
		echo "<script>top.setRecord(".$sdata['allseven'].",".$sdata['allitemh'].",".($sdata['alliteml']+1).",".$sdata['crossseven'].");</script>";
		$alll=1;
		break;
	case 4:
		slot_db_exec('UPDATE zetyx_member_table SET crossseven = crossseven + 1, rankpoint = rankpoint + 1 WHERE user_id = :user_id', [':user_id' => $slot_user_id]);
		echo "<script>top.setRecord(".$sdata['allseven'].",".$sdata['allitemh'].",".$sdata['alliteml'].",".($sdata['crossseven']+1).");</script>";
		$cro7=1;
		break;
	default :
		break;
		
}

if($total_points==0)
{
	$gamemsg="Game Over";
	echo "<script>gamemsg='".$gamemsg."';</script>";		
}
else
{
	$total_points=$total_points*$doublepoint;
    $payout_multiplier_bp = slot_setting_int('payout_multiplier_bp', 10000, 0, 100000);
    $total_points = (int)floor($total_points * $payout_multiplier_bp / 10000);
	echo "<script>gamemsg='".$gamemsg."'+' ".$total_points." P';</script>";
	$gamemsg = $gamemsg . " " . $total_points;
}	

slot_db_exec(
    'UPDATE zetyx_member_table
        SET gamepoint = gamepoint + :total_points,
            box1 = :box1, box2 = :box2, box3 = :box3, box4 = :box4, box5 = :box5,
            box6 = :box6, box7 = :box7, box8 = :box8, box9 = :box9
      WHERE user_id = :user_id',
    [':total_points' => $total_points, ':box1' => (int)$num[1], ':box2' => (int)$num[2], ':box3' => (int)$num[3],
     ':box4' => (int)$num[4], ':box5' => (int)$num[5], ':box6' => (int)$num[6], ':box7' => (int)$num[7],
     ':box8' => (int)$num[8], ':box9' => (int)$num[9], ':user_id' => $slot_user_id]
);

// 화면 스크립트 실행
echo "<script>setting(".$total_points.");</script>";

// 시상끝
}
else if($betmoney==0)
{
	echo "<script>alert('배팅머니를 설정해 주세요.');</script>";
	echo "<script>top.setPlay();</script>";
	echo "<script>top.focus();</script>";
}else {
	echo "<script>alert('게임머니가 부족합니다.');</script>";
	echo "<script>top.setPlay();</script>";
	echo "<script>top.focus();</script>";
}
?>
</head>
</html>