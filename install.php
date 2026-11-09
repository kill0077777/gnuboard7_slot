<?php
require_once __DIR__ . "/zbpath.php";
require_once $_zb_path . "_head.php";
require_once __DIR__ . "/security.php";
slot_require_login();
if (empty($member["is_admin"]) || (string)$member["is_admin"] !== "1") { http_response_code(403); exit("Admin only."); }
$act = isset($_POST["act"]) ? (string)$_POST["act"] : "";
if ($_SERVER["REQUEST_METHOD"] === "POST") { slot_require_csrf(); }
?>

<html>

<head>
<title>인디슬롯 설치</title>
<script>
<!--
function Move(value)
{
	if(value == "uninstall")
	{
		if(confirm("설치된 컬럼이 모두 제거됩니다. 계속하시겠습니까?") == false)
	  		return false;
	}
	menu.act.value = value;
	menu.submit();
}
//-->
</script>
</head>

<body bgcolor="white" text="black" link="blue" vlink="purple" alink="red">
<form name="menu" method="post" action="<?=htmlspecialchars($_SERVER["PHP_SELF"] ?? "", ENT_QUOTES, "UTF-8")?>">
<input type="hidden" name="csrf_token" value="<?=htmlspecialchars(slot_csrf_token(), ENT_QUOTES, "UTF-8")?>">
<input type="hidden" name="act">
<table style="border-collapse:collapse;" align="center" cellspacing="0" width="302">
    <tr>
        <td width="298" height="30" bgcolor="#FFF3F3" style="border-top-width:2; border-right-width:1; border-bottom-width:1; border-left-width:1; border-top-color:rgb(249,157,157); border-right-color:rgb(153,153,153); border-bottom-color:rgb(153,153,153); border-left-color:rgb(153,153,153); border-style:solid;">
            
            <p align="center"><span style="font-size:9pt;"><font color="#666666">인디슬롯 
            관리자 모드 </font></span></p>
            
        </td>
    </tr>
    
<?php
if(!$act) 
{
?>
    <tr>
        <td width="298" height="100" style="border-top-width:1; border-right-width:1; border-bottom-width:2; border-left-width:1; border-top-color:rgb(153,153,153); border-right-color:rgb(153,153,153); border-bottom-color:rgb(249,157,157); border-left-color:rgb(153,153,153); border-style:solid;">
            <p align="center"><span style="font-size:9pt;cursor:hand;" onclick=Move('install');><font color="#666666">[ 
            설치하기 ]</font></span></p>
            <p align="center"><span style="font-size:9pt;cursor:hand;" onclick=Move('uninstall');><font color="#666666">[ 
            삭제하기 ]</font></span></p>
        </td>
    </tr>
<?php
}

elseif($act == "install")
{
	$table = array("install");

	echo "<tr><td width='100%' style='border-top-width:1; border-right-width:1; border-bottom-width:2; border-left-width:1; border-top-color:rgb(153,153,153); border-right-color:rgb(153,153,153); border-bottom-color:rgb(249,157,157); border-left-color:rgb(153,153,153); border-style:solid;' align='center'>";
	
	for($i=0; $i<count($table); $i++)
		if(!file_exists($table[$i].".sql"))
		{
		 	echo "install.sql 파일이 존재하지 않습니다.";
			exit;
		}

	echo "<p> 멤버테이블을 수정합니다.</p>";

	
	for($j=0; $j<count($table); $j++)
	{
		$data = file($table[$j].".sql");
		
		echo "컬럼 변경 중입니다.";
	

		$i = 0;
		
		while($i < count($data))
			mysql_query($data[$i++]) or slot_fail_database();
		
	}
		
	echo "<p>성공적으로 설치하였습니다.</p>";
	echo "<br/>";
	echo "</td></tr>";
}
elseif($act == "uninstall")
{
	$table = array("uninstall");
	
	echo "<tr><td width='100%' style='border-top-width:1; border-right-width:1; border-bottom-width:2; border-left-width:1; border-top-color:rgb(153,153,153); border-right-color:rgb(153,153,153); border-bottom-color:rgb(249,157,157); border-left-color:rgb(153,153,153); border-style:solid;' align='center'>";

	for($i=0; $i<count($table); $i++)
		if(!file_exists($table[$i].".sql"))
		{
		 	echo "uninstall.sql 파일이 존재하지 않습니다.";
			exit;
		}

	echo "<p> 멤버테이블을 수정합니다.</p>";

	
	for($j=0; $j<count($table); $j++)
	{
		$data = file($table[$j].".sql");
		
		echo "컬럼 제거 중입니다.";
	

		$i = 0;
		
		while($i < count($data))
			mysql_query($data[$i++]) or slot_fail_database();
		
	}
	echo "<p>성공적으로 삭제하였습니다.</p>";
	echo "<br/>";
	echo "</td></tr>";
}
?>
</table>
</form>
</body>

</html>
