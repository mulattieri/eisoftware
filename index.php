<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>..::--::..</title>
<script type="text/javascript" src="includes/mootools.js"></script>
<script type="text/javascript" src="includes/ajaxbase.js"></script>
<link type="text/css" rel="stylesheet" href="includes/stile.css"/>
<script type="text/javascript" src="includes/sortabletable.js"></script>
<link type="text/css" rel="stylesheet" href="includes/sortabletable.css"  />
</head>
<body>
<!-- <form id='form_clienti' method ='post' action='<?=$_SERVER['PHP_SELF']?>'>
Filter: <select id="column">
		  				<option value="1">codicecallcenter</option>
						<option value="2">dbname</option>
						<option value="3">idazienda</option>
						<option value="4">desazienda</option>
					</select>
					<input type="text" id="keyword" />
					<input type="button" onclick="myTable.filter('form_clienti'); return false;" />
					<input type="reset" value="Clear" />
-->
<?php
include "includes/global.php";   
include "includes/function.php";   
include "version.php";
echo "<div id='header'>";
echo "<div  style='float:left;color:white;'>Esseitalia - EiSoftware</div>";
echo "<div style='float:right;font-size:12px;color:white;padding-top:15px;text-align:right'>ver. ".VERSIONE_EISOFTWARE." Database: sipadx_conf su server: ".$strServer." porta: ".$portaDB."<br><a href='info.php' style='color:cyan'>Info ?</a></div></h1>";
echo "</div>";
$boosearchip=false;
$newDbName="";

if ($newDbName=="") $newDbName=$DbName;//"sipadx_conf";

echo "<form id='form_software' method ='post' action='".$_SERVER['PHP_SELF']."'>";
$outselect="<select id='newDbName' name='newDbName'>";
$outselect.=getalldb($newDbName);
$outselect.="</select>";
echo "Database: ".$outselect."&nbsp;";
$conn_string = "host=".$strServer." port=" . $portaDB  . " dbname=" .$newDbName." user=" .$UId." password=" .$Pw."";
$link = pg_connect($conn_string);// or die("impossibile stabilire una connessione!");   
$arip=getallip();
$outselect="<select id='ipaddress' name='ipaddress'>";
$outselect.="<option value='---'>---</option>\n";
for ($j=0;$j<count($arip);$j++){
	$outselect.="<option value='".$arip[$j]."'";
	if (isset($_POST["ipaddress"])) {
		if ($arip[$j]==$_POST["ipaddress"]){ $outselect.=" selected";}
	}	
	$outselect.=">".$arip[$j]."</option>\n";
}
$outselect.="</select>";
echo "Filtro client: ".$outselect;
$subfiltro="";
if(isset($_POST["subfiltro"])){
	$subfiltro =$_POST["subfiltro"];
}
echo "&nbsp;&nbsp;Filtro generico: <input name='subfiltro' onkeyup=\"filtertable(this, 'filtrabletable', '1')\" type='text'' value='".$subfiltro."'> &nbsp;";
echo "Tipo:<select name='tipfile' id='tipfile'>
		<option value='SW' ";
if ((!isset($_POST["tipfile"]))||($_POST["tipfile"]=="SW")) echo "selected";
echo ">Software</option>
	<option value='RPT' "; 	
		if (isset($_POST["tipfile"])) echo "selected";
		//if ($_POST["tipfile"]=="RPT") echo "selected";
	echo ">report XML</option>
	</select>";
echo "<input type=submit>";
echo "</form>";
if (isset($_POST["ipaddress"])) { 
	if (($_POST["ipaddress"]!="")&&($_POST["ipaddress"]!="---")){
		$arclient=getclientversion($_POST["ipaddress"]);
		$boosearchip=true;
	}	
}

if (isset($_POST["tipfile"]) && $_POST["tipfile"] === "RPT") {
	$arfile=get_otherfiles();
}else{
	$arfile=checkdir("index.php");
}
$out="<table id='filtrabletable' class='sortabletable'>";
$out.="<tr><th>File</th><th>Versione</th><th>Md5</th><th>Rilascio</th><th>Note</th>";
if ($boosearchip){
	$out.="<th>Sul client</th>";
}
$out.="</tr>";
for ($j=0;$j<count($arfile);$j++){
	$out.="<tr><td>".$arfile[$j][0]."</td>";
	$out.="<td>".$arfile[$j][1]."</td>";
	$out.="<td>".$arfile[$j][2]."</td>";
	$out.="<td>".$arfile[$j][3]."</td>";
	$out.="<td>";
	if($arfile[$j][1]==""){
		$lastver=getversion($arfile[$j][0],"");
		$lastpac=getdata($arfile[$j][0],"");
		if ($lastver==""){
			$out.="Non presente su db";
		}else{
			$out.="<DIV STYLE='background:orange'>Diversa. Ultima versione su db:".$lastver." con rilascio: ".$lastpac."</div>";
		}
	}else{
		$out.="&nbsp;";
	}
	$out.="</td>";
	if ($boosearchip){
		$out.="<td><div>";
		$ricerca=trim($arfile[$j][0]).":".trim($arfile[$j][1]);
		if (in_array($ricerca,$arclient)){
			$out.="<DIV STYLE='background:green;width:90%;float:left;'>&nbsp;</div>";
		}else{
			$out.="<DIV STYLE='background:red;color:white;width:90%;float:left;'>Non risulta ".$ricerca."</div>";
		}
		if (isset($_POST["ipaddress"]) && ($_POST["ipaddress"]!="---"))
			$out.="<div style='float:left;width:15px;text-align:center'><a href='aj.php?ajop=DLLLOG&ip=".trim($_POST["ipaddress"])."&dll=".trim($arfile[$j][0])."' target=_blank>?</a></div>";
		$out.="</div></td>";
	}
	$out.="</tr>";
}
$out.="</table>";
echo $out;

?>

</body>
</html>
