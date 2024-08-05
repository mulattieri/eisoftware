<?php
$basepath="../";
function Checkwebapp($patfileversion,$versiontag){
	global $basepath;
	if (file_exists($basepath.$patfileversion)){
		require_once $basepath.$patfileversion;
		
		$ar=get_defined_constants();
		$out=$ar[$versiontag];
	}else
		$out="N.D.";
	return $out;
}
function ShowInfo($des,$ver,$expver,$rowtip=""){
	if ($rowtip=="")
		if (($expver!="")&&($expver!=$ver)) $expver="<p class='alert'>".$expver."</p>";
	$out ="<div style='margin:0px;border:1px solid white'>
			<div class='label' style='float:left;width:400px'>".$des."</div>
			<div class='label' style='float:left;width:200px'>".$ver."&nbsp;</div>
			<div class='label' style='float:left;width:200px'>".$expver."&nbsp;</div>
			<div style='clear:both'></div>
		   </div>";
	return $out;
	
}
function ShowHeader($des,$ver,$expver,$rowtip=""){
	$out ="<div style='margin:0px;border:1px solid white'>
			<div class='labelh' style='float:left;width:400px'>".$des."</div>
			<div class='labelh' style='float:left;width:200px'>".$ver."&nbsp;</div>
			<div class='labelh' style='float:left;width:200px'>".$expver."&nbsp;</div>
			<div style='clear:both'></div>
		   </div>";
	return $out;

}
function GetVerSipadx($prg){
	global $link;
	$sql="select (coalesce(idmajor,'')||'.'||coalesce(idminor,'')||'.'||coalesce(idrevision,'')||'.'||coalesce(idsubrevision,'')) as versione from ei_versioni where nomprogramma='".$prg."'";
	$rs = pg_query($link, $sql);
	$out="";
	while ($row = pg_fetch_array ($rs)) {
		$out.=  "".$row['versione'];
	}
	return $out;
}
function conftablenum(){
	global $strServer,$portaDB,$UId,$Pw;
	
	$tslink=pg_connect("host=".$strServer." port=" . $portaDB  . " dbname=sipadx_conf user=" .$UId." password=" .$Pw."");
	$sql="select count(*) as conta from  pg_stat_user_tables  where schemaname='public';";
	$rs = pg_query($tslink, $sql);
	$out="";
	while ($row = pg_fetch_array ($rs)) {
		$out.=  "".$row['conta'];
	}
	return $out;
}
require_once  "includes/global.php";
require_once "includes/config.php";   
require_once "includes/function.php";   
//require_once "version.php";
$conn_string = "host=".$strServer." port=" . $portaDB  . " dbname=" .$DbName." user=" .$UId." password=" .$Pw."";

$link = pg_connect($conn_string);// or die("impossibile stabilire una connessione!");   
$chk=array();
//$chk["tabineisoftware"]="5";
$ar=explode(" ",$conn_string);
$caption=str_replace("host=","",$ar[0]).":".str_replace("port=","",$ar[1])." ".str_replace("dbname=","",$ar[2]);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>..::--::..</title>
<link type="text/css" rel="stylesheet" href="includes/stile.css"/>
<style>
.label{
font-size:18px;

background: #feffff; /* Old browsers */
/* IE9 SVG, needs conditional override of 'filter' to 'none' */
background: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/Pgo8c3ZnIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgdmlld0JveD0iMCAwIDEgMSIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+CiAgPGxpbmVhckdyYWRpZW50IGlkPSJncmFkLXVjZ2ctZ2VuZXJhdGVkIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjAlIiB5MT0iMCUiIHgyPSIwJSIgeTI9IjEwMCUiPgogICAgPHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2ZlZmZmZiIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9Ijg0JSIgc3RvcC1jb2xvcj0iI2RkZjFmOSIgc3RvcC1vcGFjaXR5PSIxIi8+CiAgICA8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNhMGQ4ZWYiIHN0b3Atb3BhY2l0eT0iMSIvPgogIDwvbGluZWFyR3JhZGllbnQ+CiAgPHJlY3QgeD0iMCIgeT0iMCIgd2lkdGg9IjEiIGhlaWdodD0iMSIgZmlsbD0idXJsKCNncmFkLXVjZ2ctZ2VuZXJhdGVkKSIgLz4KPC9zdmc+);
background: -moz-linear-gradient(top,  #feffff 0%, #ddf1f9 84%, #a0d8ef 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#feffff), color-stop(84%,#ddf1f9), color-stop(100%,#a0d8ef)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #feffff 0%,#ddf1f9 84%,#a0d8ef 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #feffff 0%,#ddf1f9 84%,#a0d8ef 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #feffff 0%,#ddf1f9 84%,#a0d8ef 100%); /* IE10+ */
background: linear-gradient(to bottom,  #feffff 0%,#ddf1f9 84%,#a0d8ef 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#feffff', endColorstr='#a0d8ef',GradientType=0 ); /* IE6-8 */

padding:5px;
margin-bottom:0px;

}

.labelh{
font-size:18px;
background: #1e5799; /* Old browsers */
background: -moz-linear-gradient(top,  #1e5799 0%, #2989d8 50%, #207cca 51%, #7db9e8 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#1e5799), color-stop(50%,#2989d8), color-stop(51%,#207cca), color-stop(100%,#7db9e8)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%); /* IE10+ */
background: linear-gradient(to bottom,  #1e5799 0%,#2989d8 50%,#207cca 51%,#7db9e8 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#1e5799', endColorstr='#7db9e8',GradientType=0 ); /* IE6-9 */
padding:5px;
margin-bottom:0px;
color:#f0f9ff;
}

.alert{
color:red;
}</style>
</head>
<body>
<div style='width:850px;margin:0 auto'>
<?php echo ShowHeader($caption, "", date("d-m-Y H:i:s"),"header")?>
<?php echo ShowHeader("Controllo", "Versione rilevata", "Versione Attesa","")?>
<?php echo ShowInfo("Versione Sipadx", GetVerSipadx("sipadxusr"), "")?>
<?php echo ShowInfo("Versione Eisoftware", Checkwebapp("eisoftware/version.php","VERSIONE_EISOFTWARE"), "")?>
<?php echo ShowInfo("Tabelle in sipadx_conf", conftablenum(), $chk["tabineisoftware"])?>
<?php echo ShowInfo("Versione Marcatempo", Checkwebapp("Officina/version.php","VERSIONE_MARCATEMPO"), "")?>
<?php echo ShowInfo("Versione Sicarweb", Checkwebapp("sicarweb/version.php","VERSIONE_C2C"), "")?>
<?php echo ShowInfo("Versione C2C", Checkwebapp("c2c/version.php","VERSIONE_C2C"), "")?>
<?php echo ShowInfo("Estrazioni generiche (execute_cron)", Checkwebapp("execute_cron/version.php","EXECUTE_CRON"), "")?>
<?php echo ShowInfo("Estrazioni TOY", Checkwebapp("estrazioni_toy/version.php","VERSIONE_ESTRAZIONI_TOY"), "")?>
<?php echo ShowInfo("Estrazioni BMW", Checkwebapp("estrazioni_bmw/version.php","VERSIONE_ESTRAZIONI_BMW"), "")?>
<?php echo ShowInfo("Estrazioni MBI", Checkwebapp("estrazioni_mbi/version.php","VERSIONE_ESTRAZIONI_MBI"), "")?>
<?php echo ShowInfo("Estrazioni HAI", Checkwebapp("estrazioni_hai/version.php","VERSIONE_ESTRAZIONI_HAI"), "")?>
<?php echo ShowInfo("Campagne richiamo TOY", Checkwebapp("camrichiamo/version.php","VERSIONE_CAMRICHIAMO"), "")?>
<?php echo ShowInfo("SMS", Checkwebapp("auto_sms/versione.php","VERSIONE_SMS"), "")?>
<div class='label' style='margin-top:30px;width:820px;height:400px;overflow:auto;font-size:18px'><?php echo phpinfo()?></div>
</div>
</body>
</html>
