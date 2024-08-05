<?php
include "includes/global.php";
include "includes/function.php";
include "version.php";
$link = pg_connect($conn_string);// or die("impossibile stabilire una connessione!");
//echo "Test.".$_REQUEST["ajop"];
switch ($_REQUEST["ajop"]){
	case "DLLLOG":
		$out=getdllhistory($_REQUEST["ip"],$_REQUEST["dll"]);
		if ($out!="")
			$out="<table><tr><td colspan=3>Log per IP:".$_REQUEST["ip"]." su libreria: ".$_REQUEST["dll"]."</th></tr>".$out."</table>";
		else
			$out="No record found for ".$_REQUEST["ip"]." and ".$_REQUEST["dll"];
		break;
}
if ($out!=""){
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
	<?php
		
	echo $out;
	?></body></html><?php 
}

function getdllhistory($ip,$dll){
	global $link;
	$lsql="select * from ei_agclient where updnote~*'".$dll."' and ipaddress='".$ip."' order by datupdate";
	//echo $lsql;
	$rs = pg_query($link, $lsql);
	//$lnumrecord=pg_numrows($rs);
	//$numcol=pg_num_fields($rs);
	$out="";
	while ($row = pg_fetch_array ($rs)) {
		$out.=  "<tr><td>".$row['ipaddress']."</td><td>".$row['updnote']."</td><td>".$row['datupdate']."</td></tr>";
	
	}
	
	return $out;
}
?>