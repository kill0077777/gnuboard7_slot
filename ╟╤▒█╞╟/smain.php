<?php
require_once __DIR__ . "/zbpath.php";
require_once $_zb_path . "_head.php";
require_once __DIR__ . "/security.php";
slot_require_login();
$slot_csrf_token = slot_csrf_token();
$slot_user_id = slot_user_id();
$slot_bet_max = slot_setting_int('bet_max', 300, 20, 100000);
$datadb = slot_db_one('SELECT * FROM zetyx_member_table WHERE user_id = :user_id LIMIT 1', [':user_id' => $slot_user_id]);
if ($datadb === null) { slot_fail_database(); }
?>

<html>
<head>
<title>인디슬롯 Beta v3.0</title>
<script>
<!--
//---------------- 인디 슬롯 베타 3.0 을 이용해 주셔서 감사합니다 --------------------
// 
//  문의 사항이나 기타 건의 사항은 http://www.enjoydivx.net 의 슬롯 QnA 게시판으로
//	말씀 해 주시거나 pslc@enjoydivx.net 으로 문의 주시면 되겠습니다.
//
//	이 메세지는 삭제 하시지 마시지 말입니다. ^^ 
//------------------------------------------------------------------------------------


// 이미지 프리로딩
var pre = new Image();
pre.src="images/spin0.gif";"images/spin1.gif";"images/spin2.gif";"images/spin3.gif";"images/spin4.gif";"images/spin5.gif";"images/spin6.gif";"images/spin7.gif";"images/spin8.gif";"images/spin9.gif";"images/spin9.gif";"images/stop0.gif";"images/stop1.gif";"images/stop2.gif";"images/stop3.gif";"images/stop4.gif";"images/stop5.gif";"images/stop6.gif";"images/stop7.gif";"images/stop8.gif";"images/stop9.gif";"images/stop0.gif";

// 슬롯 스크립트
var bar=0;
var isPlay=false;	// 슬롯이 돌고 있는가
var isBet=true;		// 배팅 버튼을 누를 수 있는가

var holdable=false;	// 홀드가 가능한가
var hold1=false;	// 1번 홀드 플래그
var hold2=false;	// 2번 홀드 플래그
var hold3=false;	// 3번 홀드 플래그
var holds=0;		// 홀드 카운터 (3이면 클리어)
var betmoney=0;		// 배팅금액

var auto_on=false;	// 자동모드 초기화
var snd_on=true;	// 사운드 설정

function upmoney()	// 배팅 올리기
{
	document.getElementById("effect_hold").movie="#";
	document.getElementById("effect_hold").movie="swf/holding.swf";
	
	if(isBet)
	{
		if(betmoney<<?=$slot_bet_max?>)
			betmoney=betmoney+20;
		document.getElementById('betmoney').value=betmoney;
	}
}

function clearmoney()	// 배팅 클리어
{
	document.getElementById("effect_hold").movie="#";
	document.getElementById("effect_hold").movie="swf/holding.swf";
	
	if(isBet)
	{	
		betmoney=0;
		document.getElementById('betmoney').value=0;
	}
}

function setRecord(var1,var2,var3,var4)	// 기록표 갱신
{
	var num1=parseInt(var1);
	var num2=parseInt(var2);
	var num3=parseInt(var3);
	var num4=parseInt(var4);
	
	document.getElementById('record_all_seven').value=num1;
	document.getElementById('record_allitemh').value=num2;
	document.getElementById('record_alliteml').value=num3;
	document.getElementById('record_crossseven').value=num4;
	document.getElementById('record_total').value=(num1*5)+(num2*3)+num3+num4;
}

function setAuto()	// 자동모드 설정
{
	document.getElementById("effect_hold").movie="#";
	document.getElementById("effect_hold").movie="swf/holding.swf";
	
	if(auto_on)
	{
		auto_on=false;
		document.getElementById('auto_img').src='images/auto_off.gif';
	}
	else
	{
		auto_on=true;
		document.getElementById('auto_img').src='images/auto_on.gif';
	}
}

function getAuto()	// 자동모드 변수 가져오기
{
	return auto_on;
}

function setSound()	// 사운드 설정
{
	document.getElementById("effect_hold").movie="#";
	document.getElementById("effect_hold").movie="swf/holding.swf";
	
	if(snd_on)
	{
		snd_on=false;
		document.getElementById('snd_img').src='images/snd_off.gif';
	}
	else
	{
		snd_on=true;
		document.getElementById('snd_img').src='images/snd_on.gif';
	}
}

function getSound()	// 사운드 변수 가져오기
{
	return snd_on;
}

function setPlay()	// 새게임 가능
{
	isPlay=false;
}

function setBetTrue()	// 배팅 가능
{
	isBet=true;
}

function setBetFalse()	// 배팅 불가
{
	isBet=false;
}

function setHoldTrue()	// 홀드 가능
{
	holdable=true;
}

function setHoldFalse()	// 홀드 불가
{
	holdable=false;
}

function movePage()	// 단축키 설정
{
	var EventStatus = event.srcElement.tagName;
	if(EventStatus!='INPUT'&&EventStatus!='TEXTAREA')
	{
		if (event.keyCode=='44'||event.keyCode=='115')
			replay();
		if (event.keyCode=='49')
			holdchk(1);
		if (event.keyCode=='50')
			holdchk(2);
		if (event.keyCode=='51')
			holdchk(3);
	}
}

function replay()	// 새게임 시작
{
	if(isPlay==false)
	{
		isPlay=true;

		var form=document.createElement("form");
        form.method="post";
        form.action="sub.php";
        form.target="win";
        [["csrf_token","<?=htmlspecialchars($slot_csrf_token, ENT_QUOTES, "UTF-8")?>"],["h1",hold1],["h2",hold2],["h3",hold3],["holds",holds],["betmoney",betmoney]].forEach(function(pair){var input=document.createElement("input"); input.type="hidden"; input.name=pair[0]; input.value=pair[1]; form.appendChild(input);});
        document.body.appendChild(form);
        form.submit();
        form.remove();
	}
	hold1=false;
	hold2=false;
	hold3=false;
	holds=0;
	
}

function noEvent()	// 뒤로가기, 새로고침 방지
{
    if (event.keyCode == 116) {
        event.keyCode= 2;
        return false;
    }
    else if(event.ctrlKey && (event.keyCode==78 || event.keyCode == 82))
    {
        return false;
    }
}

function holdClear()	// 홀드 해제
{
	hold1=false;
	hold2=false;
	hold3=false;
	holds=0;
	document.getElementById("hold1").style.backgroundColor="FFDDDD";
	document.getElementById("hold2").style.backgroundColor="FFDDDD";
	document.getElementById("hold3").style.backgroundColor="FFDDDD";
}

function holdchk(arg)	// 홀드 검사
{
	
	if(!isPlay && holdable)
	{
	var num = parseInt(arg);
	
	document.getElementById("effect_hold").movie="#";
	document.getElementById("effect_hold").movie="swf/holding.swf";
	
	
	switch(num)
	{
		case 1:
			if(!hold1)
			{
				document.getElementById("hold1").style.backgroundColor="FFA1A1";
				hold1=true;
				holds++;
			}
			else
			{
				document.getElementById("hold1").style.backgroundColor="FFF0F0";
				hold1=false;
				holds--;
			}
			break;
		case 2:
			if(!hold2)
			{
				document.getElementById("hold2").style.backgroundColor="FFA1A1";
				hold2=true;
				holds++;
			}
			else
			{
				document.getElementById("hold2").style.backgroundColor="FFF0F0";
				hold2=false;
				holds--;
			}
			break;
		case 3:
			if(!hold3)
			{
				document.getElementById("hold3").style.backgroundColor="FFA1A1";
				hold3=true;
				holds++;
			}
			else
			{
				document.getElementById("hold3").style.backgroundColor="FFF0F0";
				hold3=false;
				holds--;
			}
			break;
	}
	
	if(holds>2)
	{
		holdClear();
	}
	}
	
	
}

document.onkeypress= movePage;
document.onkeydown = noEvent;

function MM_findObj(n, d) { //v4.0

var p,i,x; if(!d) d=document;

if((p=n.indexOf("?"))>0&&parent.frames.length)
{
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
}

if(!(x=d[n])&&d.all)
{
	x=d.all[n];
}
for (i=0;!x&&i<d.forms.length;i++)
{
	x=d.forms[i][n];
}	

for(i=0;!x&&d.layers&&i<d.layers.length;i++)
{	x=MM_findObj(n,d.layers[i].document);
}

if(!x && document.getElementById)
{
	x=document.getElementById(n);
}
return x;

}

function MM_showHideLayers() { //v3.0

var i,p,v,obj,args=MM_showHideLayers.arguments;

	for (i=0; i<(args.length-2); i+=3)
	{
		 if ((obj=MM_findObj(args[i]))!=null)
		 {
		 	v=args[i+2];
			if (obj.style)
			{
				obj=obj.style;
				v=(v=='show')?'visible':(v='hide')?'hidden':v;
			}
			obj.visibility=v;
		 }
	}
}

function clear_screen(){
	clearInterval(screen_info);
}

function set_screen()
{
	document.getElementById('iscrolly').src='notice.php';
	screen_info = setInterval("scrollit()",10000);
}
//-->
</script>
</head>

<body oncontextmenu="return false" leftmargin="220" marginwidth="220" bgcolor="black" >

<div align="left">
<table width="262" style="border-collapse:collapse;" cellspacing="0" cellpadding="0" background="images/slotmain.jpg">

     <tr>    

      <td align="center" height="72" width="262" colspan="3">  

    

       
            <div id="layer3" style="width:210px; height:481px; position:absolute; left:6px; top:16px; z-index:1; visibility:visible;">
	    


                    <table cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse;" width="203" bgcolor="#F1F1F1" >
                        <tr valign="top" align="center">
                            <td width="201" height="30" align="center" valign="middle" style="border-top-width:2; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,113,113); border-right-color:rgb(255,238,238); border-bottom-color:rgb(216,216,216); border-left-color:rgb(255,238,238); border-style:solid;" colspan="2" bgcolor="#FFF0F0">
                            <p><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;" onclick="tempc();"><b>CHANCE</b></span></font></p>
                            </td>
                        </tr>
                        <tr align="center" valign="top">
                            <td width="201" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-left-width:1; border-top-color:rgb(216,216,216); border-right-color:rgb(255,238,238); border-left-color:rgb(216,216,216); border-top-style:solid; border-right-style:solid; border-left-style:solid;" height="185" bgcolor="#F9F9F9" colspan="2">
                                
                    <table cellpadding="0" cellspacing="0" align="center" style="border-collapse:collapse;" width="185" >
                        <tr align="center" valign="top">
                            <td width="128" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-left-width:1; border-top-color:rgb(153,153,153); border-right-color:rgb(255,238,238); border-left-color:rgb(153,153,153); border-top-style:solid; border-right-style:solid; border-left-style:solid;" bgcolor="#F9F9F9">
                            <p><font color="#A84545"><b><span style="font-size:11pt; margin-top:0;"><img src="images/1bar.gif" border="0" width="40" height="40"><img src="images/1bar.gif" border="0" width="40" height="40"><img src="images/1bar.gif" border="0" width="40" height="40"></span></b></font></p>
                            </td>
                            <td width="54" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-left-width:1; border-top-color:rgb(153,153,153); border-right-color:rgb(153,153,153); border-left-color:rgb(255,238,238); border-top-style:solid; border-right-style:solid; border-left-style:solid;" bgcolor="#F9F9F9">
                            <p><font color="#F90000" face="Comic Sans MS"><span style="font-size:14pt; margin-top:0;"><b>x 
                                3</b></span></font></p>
                            </td>
                        </tr>
                        <tr align="center" valign="top">
                            <td width="128" align="center" valign="middle" style="border-right-width:1; border-left-width:1; border-right-color:rgb(255,238,238); border-left-color:rgb(153,153,153); border-right-style:solid; border-left-style:solid;" height="36" bgcolor="#F9F9F9">
                                <p><font color="#A84545"><b><span style="font-size:11pt; margin-top:0;"><img src="images/2bar.gif" border="0" width="40" height="40"><img src="images/2bar.gif" border="0" width="40" height="40"><img src="images/2bar.gif" border="0" width="40" height="40"></span></b></font></p>
                            </td>
                            <td width="54" align="center" valign="middle" style="border-right-width:1; border-left-width:1; border-right-color:rgb(153,153,153); border-left-color:rgb(255,238,238); border-right-style:solid; border-left-style:solid;" height="36" bgcolor="#F9F9F9">
                                <p><font color="#F90000" face="Comic Sans MS"><span style="font-size:14pt; margin-top:0;"><b>x 
                                4</b></span></font></p>
                            </td>
                        </tr>
                        <tr valign="top" align="center">
                            <td width="128" align="center" valign="middle" style="border-right-width:1; border-left-width:1; border-right-color:rgb(255,238,238); border-left-color:rgb(153,153,153); border-right-style:solid; border-left-style:solid;" height="36" bgcolor="#F9F9F9">
                                <p><font color="#A84545"><b><span style="font-size:11pt; margin-top:0;"><img src="images/3bar.gif" border="0" width="40" height="40"><img src="images/3bar.gif" border="0" width="40" height="40"><img src="images/3bar.gif" border="0" width="40" height="40"></span></b></font></p>
                            </td>
                            <td width="54" align="center" valign="middle" style="border-right-width:1; border-left-width:1; border-right-color:rgb(153,153,153); border-left-color:rgb(255,238,238); border-right-style:solid; border-left-style:solid;" height="36" bgcolor="#F9F9F9">
                                <p><font color="#F90000" face="Comic Sans MS"><span style="font-size:14pt; margin-top:0;"><b>x 
                                5</b></span></font></p>
                            </td>
                        </tr>
                        <tr valign="top" align="center">
                            <td width="128" align="center" valign="middle" style="border-right-width:1; border-bottom-width:1; border-left-width:1; border-right-color:rgb(255,238,238); border-bottom-color:rgb(153,153,153); border-left-color:rgb(153,153,153); border-right-style:solid; border-bottom-style:solid; border-left-style:solid;" height="36" bgcolor="#F9F9F9">
                            <p><img src="images/superbell.gif" border="0" width="40" height="40"><img src="images/superbell.gif" border="0" width="40" height="40"><img src="images/superbell.gif" border="0" width="40" height="40"></p>
                            </td>
                            <td width="54" align="center" valign="middle" style="border-right-width:1; border-bottom-width:1; border-left-width:1; border-right-color:rgb(153,153,153); border-bottom-color:rgb(153,153,153); border-left-color:rgb(255,238,238); border-right-style:solid; border-bottom-style:solid; border-left-style:solid;" height="36" bgcolor="#F9F9F9">
                                <p><font color="#F90000" face="Comic Sans MS"><span style="font-size:14pt; margin-top:0;"><b>x 
                                5</b></span></font></p>
                            </td>
                        </tr>
			
			
			<OBJECT id="effect_spin" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<param name=autostart value=false>
<PARAM NAME=movie VALUE="swf/spin.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>

<OBJECT id="effect_hold" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<param name=autostart value=false> 
<PARAM NAME=movie VALUE="swf/holding.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>

<OBJECT id="effect_swin" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<param name=autostart value=false>
<PARAM NAME=movie VALUE="swf/swin.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>

<OBJECT id="effect_mwin" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<param name=autostart value=false>
<param name="Volume" value="100%">
<PARAM NAME=movie VALUE="swf/mwin.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>

<OBJECT id="effect_bwin" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<param name=autostart value=false>
<PARAM NAME=movie VALUE="swf/bwin.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>

<OBJECT id="effect_over" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<param name=autostart value=false>
<PARAM NAME=movie VALUE="swf/gameover.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>

<OBJECT id="effect_jp" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" WIDTH=0 HEIGHT=0>
<PARAM NAME=movie VALUE="swf/gameover.swf">
<param name=autostart value=false>
<PARAM NAME=quality VALUE=high>
<PARAM NAME=wmode value=opaque>
<PARAM NAME=hidden VALUE=true>
<PARAM NAME=loop value=false>
</OBJECT>


                    </table>


                            </td>
                        </tr>
                        <tr valign="top" align="center">
                            <td width="201" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-color:rgb(216,216,216); border-style:solid;" height="30" colspan="2" bgcolor="#FFF0F0">
                            <p><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;"><b>RECORDS</b></span></font></p>
                            </td>
                        </tr>
                        <tr>
                            <td width="128" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(216,216,216); border-right-color:rgb(255,238,238); border-bottom-color:rgb(255,238,238); border-left-color:rgb(216,216,216); border-style:solid;" height="27" bgcolor="#F9F9F9">
                            <p><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;"><b>All 
                                Seven</b></span></font></p>
                            </td>
                            <td width="72" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(216,216,216); border-right-color:rgb(216,216,216); border-bottom-color:rgb(255,238,238); border-left-color:rgb(255,238,238); border-style:solid;" height="27" bgcolor="#F9F9F9">
                                <p align="center"><span style="font-size:10pt;">&nbsp;&nbsp;<input type=text maxlength="4" size="4" name="record_all_seven" style="font-weight:bold; font-size:10pt; color:red; text-align:left; background-color:rgb(249,249,249); border-width:0; border-style:none;" READONLY value="<?=$datadb['allseven']?>"></span>           </td>
                        </tr>
                        <tr>
                            <td width="128" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(255,238,238); border-bottom-color:rgb(255,238,238); border-left-color:rgb(216,216,216); border-style:solid;" height="27" bgcolor="#F9F9F9">
                            <p><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;"><b>All 
                                same (High)</b></span></font></p>
                            </td>
                            <td width="72" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(216,216,216); border-bottom-color:rgb(255,238,238); border-left-color:rgb(255,238,238); border-style:solid;" height="27" bgcolor="#F9F9F9">
                                <p align="center"><span style="font-size:10pt;">&nbsp;&nbsp;<input type=text maxlength="4" size="4" name="record_allitemh" style="font-weight:bold; font-size:10pt; color:red; text-align:left; background-color:rgb(249,249,249); border-width:0; border-style:none;" READONLY value="<?=$datadb['allitemh']?>"></span>                            </td>
                        </tr>
                        <tr>
                            <td width="128" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(255,238,238); border-bottom-color:rgb(255,238,238); border-left-color:rgb(216,216,216); border-style:solid;" height="27" bgcolor="#F9F9F9">
                            <p><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;"><b>All 
                                same (Low)</b></span></font></p>
                            </td>
                            <td width="72" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(216,216,216); border-bottom-color:rgb(255,238,238); border-left-color:rgb(255,238,238); border-style:solid;" height="27" bgcolor="#F9F9F9">
                                <p align="center"><span style="font-size:10pt;">&nbsp;&nbsp;<input type=text maxlength="4" size="4" name="record_alliteml" style="font-weight:bold; font-size:10pt; color:red; text-align:left; background-color:rgb(249,249,249); border-width:0; border-style:none;" READONLY value="<?=$datadb['alliteml']?>"></span>                            </td>
                        </tr>
                        <tr valign="top" align="center">
                            <td width="128" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(255,238,238); border-bottom-color:rgb(255,238,238); border-left-color:rgb(216,216,216); border-style:solid;" height="27" bgcolor="#F9F9F9">
                            <p><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;"><b>Cross 
                                Seven</b></span></font></p>
                            </td>
                            <td width="72" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(216,216,216); border-bottom-color:rgb(255,238,238); border-left-color:rgb(255,238,238); border-style:solid;" height="27" bgcolor="#F9F9F9">
                                <p align="center"><span style="font-size:10pt;">&nbsp;&nbsp;<input type=text maxlength="4" size="4" name="record_crossseven" style="font-weight:bold; font-size:10pt; color:red; text-align:left; background-color:rgb(249,249,249); border-width:0; border-style:none;" READONLY value="<?=$datadb['crossseven']?>"></span>                            </td>
                        </tr>
                        <tr>
                            <td width="128" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(255,238,238); border-bottom-color:rgb(216,216,216); border-left-color:rgb(216,216,216); border-style:solid;" height="27" bgcolor="#F9F9F9">
                            <p><font color="#F62020" face="Comic Sans MS"><span style="font-size:11pt; margin-top:0;"><b>Rank 
                                Points</b></span></font></p>
                            </td>
                            <td width="72" align="center" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(255,238,238); border-right-color:rgb(216,216,216); border-bottom-color:rgb(216,216,216); border-left-color:rgb(255,238,238); border-style:solid;" height="27" bgcolor="#F9F9F9">
                                <p align="center"><span style="font-size:10pt;">&nbsp;&nbsp;<input type=text maxlength="4" size="4" name="record_total" style="font-weight:bold; font-size:10pt; color:red; text-align:left; background-color:rgb(249,249,249); border-width:0; border-style:none;" READONLY value="<?=($datadb['allseven']*5)+($datadb['allitemh']*3)+($datadb['alliteml']+$datadb['crossseven'])?>"></span>                            </td>
                        </tr>
                        <tr valign="top" align="center">
                            <td width="201" align="left" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:1; border-color:rgb(216,216,216); border-style:solid;" height="30" colspan="2" bgcolor="#FFF0F0">
                                <p align="center"><b><font color="#FF4D4D" face="Comic Sans MS"><span style="font-size:11pt;">HOTKEY</span></font></b></p>
                            </td>
                        </tr>
                        <tr valign="top" align="center">
                            <td width="201" align="left" valign="middle" style="border-top-width:1; border-right-width:1; border-bottom-width:2; border-left-width:1; border-top-color:rgb(216,216,216); border-right-color:rgb(216,216,216); border-bottom-color:rgb(255,113,113); border-left-color:rgb(216,216,216); border-style:solid;" height="70" colspan="2" bgcolor="#F9F9F9">
                                <p><b><font color="red" face="Comic Sans MS"><span style="font-size:11pt;">&nbsp;&nbsp;&nbsp;1 
                                - 
                            </span></font><font color="#993300" face="Comic Sans MS"><span style="font-size:11pt;">Hold1 
                                &nbsp;</span></font><font color="red" face="Comic Sans MS"><span style="font-size:11pt;">2 
                                - </span></font><font color="#993300" face="Comic Sans MS"><span style="font-size:11pt;">Hold2 
                                <br> 
                                &nbsp;&nbsp;&nbsp;</span></font><font color="red" face="Comic Sans MS"><span style="font-size:11pt;">3 
                                - 
                            </span></font><font color="#993300" face="Comic Sans MS"><span style="font-size:11pt;">Hold3 
                                &nbsp;</span></font><font color="red" face="Comic Sans MS"><span style="font-size:11pt;">S 
                                -</span></font><font color="#993300" face="Comic Sans MS"><span style="font-size:11pt;"> 
                            START</span></font></b></p>
                            </td>
                        </tr>
                    </table>
            </div>


</td>

</tr>

     <tr>    

      <td align="center" height="257" width="36" rowspan="5">  

                   
                <p style="border-collapse:collapse;" align="center">&nbsp;</p>
</td>

      <td align="center" height="257" width="190" rowspan="5">  

                   
<table align=center width="179" style="border-collapse:collapse;" cellspacing="0">
<tr>
	<td width="66" name="status" align=center valign="middle" style="border-width:0; border-style:none;" height="40">
           <div id="layer2" style="width:138px; height:138px; position:absolute; left:284px; top:159px; z-index:2; visibility:hidden;" align=center> 
		<iframe name="snd" src="#" width="138" height="138" scrolling="no" frameborder="0" align="center" valign="middle"></iframe>         
            </div></td>
	<td width="109" name="status" align=center valign="middle" style="border-width:0; border-style:none;" height="40">
            <p align="right"><input type="text" name="betmoney" value="0" maxlength="4" size="4" style="font-family:'Comic Sans MS'; font-weight:bold; font-size:9pt; color:rgb(238,161,94); text-align:center; background-color:rgb(136,25,16); border-width:1; border-color:rgb(69,8,3); border-style:solid;" READONLY> 
                            &nbsp;</p>
</td>
</tr>

<tr>
	<td width="169" name="status" height="189" align="left" valign="top" style="text-indent:0; margin:0; padding-top:10; padding-right:0; padding-bottom:0; padding-left:10; border-width:0; border-style:none;" colspan="2">

<div id="layer1" z-index:1; visibility:show;" align="left">

	       <table width="156" style="margin-left:2; border-collapse:collapse;" cellspacing="0" cellpadding="1">
                    <tr>
                        <td id="lamp4" width="17" height="18" bordercolor="#6633FF" style="border-width:1; border-top-color:rgb(184,94,92); border-right-color:rgb(184,106,106); border-bottom-color:rgb(184,106,106); border-left-color:rgb(184,94,92); border-style:solid;" bgcolor="#FFF0F0" align="center" valign="middle">
                            <p align="center"><font face="Arial Black" color="#990033"><span style="font-size:7pt;">4</span></font></p>
</td>
                        <td id="lamp6" width="40" height="18" bordercolor="#6633FF" style="border-top-width:1; border-right-width:2; border-bottom-width:1; border-left-width:1; border-top-color:rgb(184,94,92); border-right-color:rgb(184,106,106); border-bottom-color:rgb(102,102,102); border-left-color:rgb(184,106,106); border-style:solid;" bgcolor="#FFDDDD" align="center" valign="middle">
                            <p align="center"><font face="Arial Black" color="#990033"><span style="font-size:7pt;">6</span></font></p>
</td>
                        <td id="lamp7" width="40" height="18" bordercolor="#6633FF" style="border-top-width:1; border-right-width:2; border-bottom-width:1; border-left-width:2; border-top-color:rgb(184,94,92); border-right-color:rgb(184,106,106); border-bottom-color:rgb(102,102,102); border-left-color:rgb(184,106,106); border-style:solid;" bgcolor="#FFDDDD" align="center" valign="middle">
                            <p align="center"><font face="Arial Black" color="#990033"><span style="font-size:7pt;">7</span></font></p>
</td>
                        <td id="lamp8" width="42" height="18" bordercolor="#6633FF" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:2; border-top-color:rgb(184,94,92); border-right-color:rgb(184,94,92); border-bottom-color:rgb(102,102,102); border-left-color:rgb(184,106,106); border-style:solid;" bgcolor="#FFDDDD" align="center" valign="middle">
                            <p align="center"><font face="Arial Black" color="#990033"><span style="font-size:7pt;">8</span></font></p>
</td>
                    </tr>

                    <tr>
                        <td id="lamp2" width="17" height="42" bordercolor="#6633FF" style="border-top-width:1; border-right-width:1; border-bottom-width:2; border-left-width:1; border-top-color:rgb(184,106,106); border-right-color:rgb(102,102,102); border-bottom-color:rgb(184,106,106); border-left-color:rgb(184,94,92); border-style:solid;" bgcolor="#FFDDDD" align="center" valign="middle">
                            <p align="center"><span style="font-size:7pt;"><font face="Arial Black" color="#990033">2</font></span></p>
</td>
                        <td id="slot1" width="40" height="42" bordercolor="#6633FF" style="border-top-width:1; border-right-width:2; border-bottom-width:2; border-left-width:1; border-color:rgb(102,102,102); border-style:solid;" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i1"></font></span></td>
                        <td id="slot2" width="40" height="42" bordercolor="#6633FF" style="border-top-width:1; border-right-width:2; border-bottom-width:2; border-left-width:2; border-color:rgb(102,102,102); border-style:solid;" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i2"></font></span></td>
                        <td id="slot3" width="40" height="42" bordercolor="#6633FF" style="border-top-width:1; border-right-width:1; border-bottom-width:2; border-left-width:2; border-top-color:rgb(102,102,102); border-right-color:rgb(184,94,92); border-bottom-color:rgb(102,102,102); border-left-color:rgb(102,102,102); border-style:solid;" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i3"></font></span></td>
                    </tr>

                    <tr>
                        <td id="lamp1" width="17" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:1; border-bottom-width:2; border-left-width:1; border-top-color:rgb(184,106,106); border-right-color:rgb(102,102,102); border-bottom-color:rgb(184,106,106); border-left-color:rgb(184,94,92); border-style:solid;" bgcolor="#FFDDDD" align="center" valign="middle">
                            <p align="center"><span style="font-size:7pt;"><font face="Arial Black" color="#990033">1</font></span></p>
</td>
                        <td id="slot4" width="40" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:2; border-bottom-width:2; border-left-width:1; border-color:rgb(102,102,102); border-style:solid;" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i4"></font></span></td>
                        <td id="slot5" width="40" height="42" bordercolor="#6633FF" style="border-width:2; border-color:rgb(102,102,102); border-style:solid;" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i5"></font></span></td>
                       <td id="slot6" width="40" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:1; border-bottom-width:2; border-left-width:2; border-top-color:rgb(102,102,102); border-right-color:rgb(184,94,92); border-bottom-color:rgb(102,102,102); border-left-color:rgb(102,102,102); border-style:solid;" bgcolor="white" >
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i6"></font></span></td>
                    </tr>

                    <tr>
                        <td id="lamp3" width="17" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(184,106,106); border-right-color:rgb(102,102,102); border-bottom-color:rgb(184,106,106); border-left-color:rgb(184,94,92); border-style:solid;" height="28" bgcolor="#FFDDDD" align="center" valign="middle">
                            <p align="center"><span style="font-size:7pt;"><font face="Arial Black" color="#990033">3</font></span></p>
</td>
                        <td id="slot7" width="40" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:2; border-bottom-width:1; border-left-width:1; border-color:rgb(102,102,102); border-style:solid;" height="28" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i7"></font></span></td>
                        <td id="slot8" width="40" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:2; border-bottom-width:1; border-left-width:2; border-color:rgb(102,102,102); border-style:solid;" height="28" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i8"></font></span></td>
                        <td id="slot9" width="40" height="42" bordercolor="#6633FF" style="border-top-width:2; border-right-width:1; border-bottom-width:1; border-left-width:2; border-top-color:rgb(102,102,102); border-right-color:rgb(184,94,92); border-bottom-color:rgb(102,102,102); border-left-color:rgb(102,102,102); border-style:solid;" height="28" bgcolor="white">
                                            <p align="center"><span style="font-size:8pt;"><font face="Arial Black" color="#990033"><img src="images/stop5.gif" width="40" height="40" name="i9"></font></span></td>
                    </tr>

                    <tr>
                        <td id="lamp5" width="17" height="20" bordercolor="#6633FF" style="border-width:1; border-top-color:rgb(184,106,106); border-right-color:rgb(184,106,106); border-bottom-color:rgb(184,94,92); border-left-color:rgb(184,94,92); border-style:solid;" height="20" bgcolor="#FFF0F0" align="center" valign="middle">
                            <p align="center"><span style="font-size:7pt;"><font face="Arial Black" color="#990033">5</font></span></p>
</td>
                        <td id="hold1" width="42" height="20" bordercolor="#6633FF" style="border-top-width:1; border-right-width:0; border-bottom-width:1; border-left-width:1; border-top-color:rgb(102,102,102); border-bottom-color:rgb(184,94,92); border-left-color:rgb(184,106,106); border-top-style:solid; border-right-style:none; border-bottom-style:solid; border-left-style:solid;" height="20" bgcolor="#FFDDDD">
                
                            <p align="center"><span id="holdtext1" style="font-size:9pt;visibility:hidden;"><b><font color="#810B2E">Hold</font></b></span></td>
                        <td id="hold2" width="42" height="20" bordercolor="#6633FF" style="border-top-width:1; border-right-width:0; border-bottom-width:1; border-left-width:0; border-top-color:rgb(102,102,102); border-bottom-color:rgb(184,94,92); border-left-color:rgb(184,106,106); border-top-style:solid; border-right-style:none; border-bottom-style:solid; border-left-style:none;" height="20" bgcolor="#FFDDDD">
                
                            <p align="center"><span id="holdtext2" style="font-size:9pt;visibility:hidden;"><b><font color="#810B2E">Hold</font></b></span></td>
                        <td id="hold3" width="44" height="20" bordercolor="#6633FF" style="border-top-width:1; border-right-width:1; border-bottom-width:1; border-left-width:0; border-top-color:rgb(102,102,102); border-right-color:rgb(184,94,92); border-bottom-color:rgb(184,94,92); border-left-color:rgb(184,106,106); border-top-style:solid; border-right-style:solid; border-bottom-style:solid; border-left-style:none;" height="20" bgcolor="#FFDDDD">
                
                            <p align="center"><span id="holdtext3" style="font-size:9pt;visibility:hidden;"><b><font color="#810B2E">Hold</font></b></span></td>
                    </tr>
                </table>
</div>
   			
        </td>
</tr>

<tr>
	<td width="169" name="status" height="41" align="left" valign="middle" style="text-indent:0; margin:0; padding-top:10; padding-right:0; padding-bottom:0; padding-left:10; border-width:0; border-style:none;" colspan="2">

                            <p align="center"><input type="text" name="bar" size="18" style="font-family:Arial; font-weight:bold; font-size:12; color:rgb(163,1,1); text-align:center; background-color:rgb(252,242,243); border-width:2; border-color:rgb(208,111,108); border-style:solid;" READONLY><br><font size="2">&nbsp;</font>        </td>
</tr>

<tr>
	<td width="66" height="90" style="letter-spacing:0; border-width:0; border-style:none;" valign="middle">
            <p align="center">&nbsp;</p>
</td>
	<td width="109" height="90" style="letter-spacing:0; border-width:0; border-style:none;" valign="bottom">
            <p align="center"><input type="text" name="jackpottxt" value="0" maxlength="12" size="12" style="font-family:'Comic Sans MS'; font-weight:bold; font-size:9pt; color:rgb(68,2,3); text-align:center; background-color:rgb(215,150,66); border-width:1; border-color:rgb(69,8,3); border-style:solid;" READONLY><br><input type="text" name="moneytxt" value="0" maxlength="12" size="12" style="font-family:'Comic Sans MS'; font-weight:bold; font-size:9pt; color:rgb(68,2,3); text-align:center; background-color:rgb(215,150,66); border-width:1; border-color:rgb(69,8,3); border-style:solid;" READONLY><br><input type="text" name="belltxt" value="0" maxlength="12" size="12" style="font-family:'Comic Sans MS'; font-weight:bold; font-size:9pt; color:rgb(68,2,3); text-align:center; background-color:rgb(215,150,66); border-width:1; border-color:rgb(69,8,3); border-style:solid;" READONLY><br><input type="text" name="servicetxt" value="0" maxlength="12" size="12" style="font-family:'Comic Sans MS'; font-weight:bold; font-size:9pt; color:rgb(68,2,3); text-align:center; background-color:rgb(215,150,66); border-width:1; border-color:rgb(69,8,3); border-style:solid;" READONLY></p>
</td>
</tr>
</table>


</td>

      <td align="center" height="23" width="36">  

                   
                <p align="center"><img src="images/up.gif" border="0" style="cursor:hand" onclick="upmoney()"></p>
</td>
</tr>
<tr>
      <td align="center" width="36">  
                <p align="center"><img src="images/clear.gif" width="30" height="30" border="0" style="cursor:hand" onclick="clearmoney()"></p>
</td>

    </tr>
     <tr>    

      <td align="center" width="36">  
                <p><img id="snd_img" src="images/snd_on.gif" width="30" height="30" border="0" onclick="setSound();"></p>
</td>

</tr>

   
<tr>
      <td align="center" width="36">  
                <p><img id="auto_img" src="images/auto_off.gif" width="30" height="30" border="0" onclick="setAuto();"></p>
</td>

    </tr>
     <tr>    

      <td align="center" width="36">  

                   
                <p>&nbsp;&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
</td>

</tr>

   
    <tr>



        <td style="border-bottom-width:0; border-bottom-style:none;" align="center" valign="bottom" width="262" height="62" colspan="3">




            <p align="center"><img src="images/hold1.gif" border="0" onclick="holdchk(1);" width="60" height="60" style="cursor:hand"> 
                <img src="images/hold2.gif" border="0" onclick="holdchk(2);" width="60" height="60" style="cursor:hand"> 
                <img src="images/hold3.gif" border="0" onclick="holdchk(3);" width="60" height="60" style="cursor:hand"> 
                <img id="spinbtn" src="images/spin.gif" border="0" onclick="replay();" name="image1" width="60" height="60" style="cursor:hand"><iframe src="#" name=win width="0" height="0" scrolling=no align=center marginwidth="0" marginheight="0" frameborder="no"></iframe>
	
        </td>
    </tr>
</table>
</div>







