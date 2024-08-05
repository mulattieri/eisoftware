<?php
$localpath="";
if (is_file($localpath."includes/config.php")) {
	require $localpath."includes/config.php";
}
if (is_file($localpath."includes/esseitalia_config.php")) {
	require $localpath."includes/esseitalia_config.php";
}
if (is_file($localpath."includes/international_config.php")) {
	require $localpath."includes/international_config.php";
	//parametri standard per la versione international
	$conn_string = "host=".$strServer." port=" . $portaDB  . " dbname=" .$DbName." user=" .$UId." password=" .$Pw."";
	$link_conf= pg_connect("host=".$strServer." port=" . $portaDB  . " dbname=sipadx_conf_international user=" .$UId." password=" .$Pw."") or die("immpossibile stabilire una connessione con sipadx_conf!");
	define('CHECK_PATH','/sipadxcomune/lastversion/aggiornamentosw');
	define('CHECK_PATH_OTHER','');
	define('LASTVERSION','/sipadxcomune/lastversion_international');
	define('CHECK_FILE','/sipadxcomune/lastversion/aggiornamentosw/0000reg-software.txt');
	define('CHECK_FILE_OTHER','/sipadxcomune/lastversion/aggiornamentorpt/0000reg-software.txt');
}else{
	//parametri standard
	// Suffisso tabelle
	
	$conn_string = "host=".$strServer." port=" . $portaDB  . " dbname=" .$DbName." user=" .$UId." password=" .$Pw."";
	$downloadDir="downloads";
	$pathSystem="/";
	//$link = pg_connect($conn_string) or die("immossibile stabilire una connessione!");    
	$link_conf= pg_connect("host=".$strServer." port=" . $portaDB  . " dbname=sipadx_conf user=" .$UId." password=" .$Pw."") or die("immpossibile stabilire una connessione con sipadx_conf!");
	define('CHECK_PATH','/sipadxcomune/lastversion/aggiornamentosw');
	define('CHECK_PATH_OTHER','/sipadxcomune/lastversion/aggiornamentorpt');
	define('LASTVERSION','/sipadxcomune/lastversion');
	define('CHECK_FILE','/sipadxcomune/lastversion/aggiornamentosw/0000reg-software.txt');
	define('CHECK_FILE_OTHER','/sipadxcomune/lastversion/aggiornamentorpt/0000reg-software.txt');
}
function GetHDCode($StringToAnalize) {
	$lastChar = -1;
	$strToReturn = '';
	$PosizioneInizio = 2;
	if (substr($StringToAnalize, 0, 3) == "HD ") {
		$PosizioneInizio  = 3;
	}

	for ($x=$PosizioneInizio; $x<strlen($StringToAnalize); $x++) {
		$char=substr($StringToAnalize, $x, 1);
		if (strpos('0123456789', $char) === false) {
			$x=strlen($StringToAnalize); 
		} else {
			$lastChar = $x;
		}
             }

	if ($lastChar !== -1) {
		$strToReturn = substr($StringToAnalize, $PosizioneInizio , $lastChar -  $PosizioneInizio +1);
	} 
	return $strToReturn;
}

function StringConverter($StringToConvert) {
	$NewString = $StringToConvert;
	$NewString = str_replace('à', 'a\'', $NewString);
	$NewString = str_replace('è', 'e\'', $NewString);
	$NewString = str_replace('é', 'e\'', $NewString);
	$NewString = str_replace('ì', 'i\'', $NewString);
	$NewString = str_replace('ò', 'o\'', $NewString);
	$NewString = str_replace('ù', 'u\'', $NewString);
	$NewString = str_replace(';', '', $NewString);
	$NewString = str_replace('\r\n', '\n', $NewString);
	$NewString = str_replace('\n', '', $NewString);
	$NewString = str_replace(chr(13), '', $NewString);
	$NewString = str_replace(chr(10), '', $NewString);
	$NewString = str_replace('"', '', $NewString);
	return $NewString ;
}


function getIPClient() {
    return $_SERVER["REMOTE_ADDR"]; 
}

function getUAClient() {
    return  $_SERVER["HTTP_USER_AGENT"];       
}

function getTabRelease($msg) {
    global $conn_string;
    $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");           
    $ssql = "select * from rilascirelease where stato = 1 order by datarelease desc";
    $result = pg_query($link, $ssql);
    $lnumrecord=pg_numrows($result);
    $counter=0;
    $sOut ="";
    
    if (trim($msg) <> "") {
        $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\">";
        $sOut = $sOut. "<tr><td colspan=\"3\" class=\"tbltdcol2\">".$msg."</td></tr>";
        $sOut = $sOut."</table>";    
    }
    $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\" class=\"tablestyle2\">";
    $sOut = $sOut. "<tr class=\"tblheader2\"><td>Ver. Release</td><td>Data</td><td>Downloads</td></tr>";
    if ($lnumrecord > 0) {
        while ($row = pg_fetch_array($result)) {
        if (fmod($counter, 2) == 0) {
             $sOut = $sOut. "<tr class=\"tbltdcol1\"><td>".getLinkDownRelease(903, $row['idrelease'], $row['numrilascio'])."</td><td>".formatDateView($row['datarelease'], "/", false)."</td><td>".checkDownloads($row['idrelease'], $row['numrilascio'])."</td></tr>";
        }else{
             $sOut = $sOut. "<tr class=\"tbltdcol2\"><td>".getLinkDownRelease(903, $row['idrelease'], $row['numrilascio'])."</td><td>".formatDateView($row['datarelease'], "/", false)."</td><td>".checkDownloads($row['idrelease'], $row['numrilascio'])."</td></tr>";
        } 
        $counter = $counter+1;
        }
    }else{
         $sOut = $sOut. "<tr class=\"tbltdcol1\"><td colspan=\"3\">Non esistono release disponibili</td></tr>";
    }
    pg_close($link);
    $sOut = $sOut."</table>";
return $sOut;
}

function getTabListaClienti($msg) {
    global $conn_string;
    $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");           
    $ssql = "  select distinct tblanagraficaclienti.ragionesociale, tblanagraficaclienti.codicecallcenter, tblmarca.descrizione,";
    $ssql.= "  infocar.attivo as infocar, c2c.attivo as c2c, au.attivo as au, listino.attivo as listino, crontab.attivo as crontab,";
    $ssql.= "  sipadx.attivo as sipadx";
    $ssql.= "  from tblanagraficaclienti ";
    $ssql.= "  INNER JOIN tblmarca on tblmarca.marcaid =tblanagraficaclienti.marcaid ";
    $ssql.= "  INNER JOIN sipadx_configcliente on sipadx_configcliente.codicecallcenter =tblanagraficaclienti.codicecallcenter ";
    $ssql.= "  LEFT  JOIN sipadx_configcliente as infocar on  infocar.codicecallcenter=tblanagraficaclienti.codicecallcenter and infocar.flusso='infocar' and infocar.attivo=1";
    $ssql.= "  LEFT  JOIN sipadx_configcliente as c2c on  c2c.codicecallcenter=tblanagraficaclienti.codicecallcenter and c2c.flusso='c2c' and c2c.attivo=1";
    $ssql.= "  LEFT  JOIN sipadx_configcliente as au on  au.codicecallcenter=tblanagraficaclienti.codicecallcenter and au.flusso='au' and au.attivo=1";
    $ssql.= "  LEFT  JOIN sipadx_configcliente as listino on  listino.codicecallcenter=tblanagraficaclienti.codicecallcenter and listino.flusso='listino' and listino.attivo=1";
    $ssql.= "  LEFT  JOIN sipadx_configcliente as crontab on  crontab.codicecallcenter=tblanagraficaclienti.codicecallcenter and crontab.flusso='crontab' and crontab.attivo=1";
    $ssql.= "  LEFT  JOIN sipadx_configcliente as sipadx on  sipadx.codicecallcenter=tblanagraficaclienti.codicecallcenter and sipadx.flusso='sipadx' and sipadx.attivo=1";
    $ssql.= "  where tblanagraficaclienti.codicecallcenter <> 0 and tblanagraficaclienti.codicecallcenter <> '' and (infocar.attivo = 1 or c2c.attivo= 1 or au.attivo= 1 or listino.attivo= 1 or crontab.attivo= 1 or sipadx.attivo=1)";
    $ssql.= "  group by tblanagraficaclienti.ragionesociale, tblanagraficaclienti.codicecallcenter, tblmarca.descrizione,";
    $ssql.= "  infocar.attivo, c2c.attivo, au.attivo, listino.attivo, crontab.attivo, sipadx.attivo";
    $ssql.= "  order by tblmarca.descrizione, tblanagraficaclienti.ragionesociale  ";

    $result = pg_query($link, $ssql);
    $lnumrecord=pg_numrows($result);
    $counter=0;               
    $sOut ="";
    if (trim($msg) <> "") {
        $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\">";
        $sOut = $sOut. "<tr><td colspan=\"3\" class=\"tbltdcol2\">".$msg." (".$lnumrecord.")<br><br></td></tr>";
        $sOut = $sOut."</table>";    
    }
    $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\" class=\"tablestyle2\">";
    $sOut = $sOut. "<tr class=\"tblheader2\"><td>Id Marca</td><td>Ragione Sociale</td><td>Codice CallCenter</td><td>Infocar<td><td>C2C<td><td>Dr.Aus<td><td>Crontab<td><td>Lsitini<td><td>Sipadx<td></tr>";
    if ($lnumrecord > 0) {
        while ($row = pg_fetch_array($result)) {
            if ($clienteid == "9889") {
                $strJscriptT="onclick=\"javascript:if(confirm('Proseguendo verrà eliminato il flusso infocar per il cliente ".$row['ragionesociale'].". \\n Proseguire ?')) {alert('Moo Tolgo')}\"";
                $strJscriptM="onclick=\"javascript:if(confirm('Proseguendo verrà eliminato il flusso infocar per il cliente ".$row['ragionesociale'].". \\n Proseguire ?')) {alert('Moo Metto')}\"";
            }
            if ($row['infocar'] == 1) {
                $infocar="<img src=\"images/checked.gif\" ".$strJscriptT.">";
            }else{
                $infocar="<img src=\"images/unchecked.png\" ".$strJscriptM.">";            
            }
            if ($row['c2c'] == 1) {
                $c2c="<img src=\"images/checked.gif\" ".$strJscriptT.">";
            }else{
                $c2c="<img src=\"images/unchecked.png\" ".$strJscriptM.">";            
            }
            if ($row['au'] == 1) {
                $au="<img src=\"images/checked.gif\" ".$strJscriptT.">";
            }else{
                $au="<img src=\"images/unchecked.png\" ".$strJscriptM.">";            
            }
            if ($row['crontab'] == 1) {
                $crontab="<img src=\"images/checked.gif\" ".$strJscriptT.">";
            }else{
                $crontab="<img src=\"images/unchecked.png\" ".$strJscriptM.">";            
            }
            if ($row['listino'] == 1) {
                $listino="<img src=\"images/checked.gif\" ".$strJscriptT.">";
            }else{
                $listino="<img src=\"images/unchecked.png\" ".$strJscriptM.">";            
            }
            if ($row['sipadx'] == 1) {
                $sipadx="<img src=\"images/checked.gif\" ".$strJscriptT.">";
            }else{
                $sipadx="<img src=\"images/unchecked.png\" ".$strJscriptM.">";            
            }
        if (fmod($counter, 2) == 0) {
             $sOut = $sOut. "<tr class=\"tbltdcol1\"><td align=\"left\">".$row['descrizione']."</td><td align=\"left\">".$row['ragionesociale']."</td><td>".$row['codicecallcenter']."</td><td>".$infocar."<td><td>".$c2c."<td><td>".$au."<td><td>".$crontab."<td><td>".$listino."<td><td>".$sipadx."<td></tr>";
        }else{
             $sOut = $sOut. "<tr class=\"tbltdcol2\"><td align=\"left\">".$row['descrizione']."</td><td align=\"left\">".$row['ragionesociale']."</td><td>".$row['codicecallcenter']."</td><td>".$infocar."<td><td>".$c2c."<td><td>".$au."<td><td>".$crontab."<td><td>".$listino."<td><td>".$sipadx."<td></tr>";
        } 
        $counter = $counter+1;
        }
    }else{
         $sOut = $sOut. "<tr class=\"tbltdcol1\"><td colspan=\"8\">Non esistono clienti attivi</td></tr>";
    }
    pg_close($link);
    $sOut = $sOut."</table>";
return $sOut;
}



function getTabInstallazioni($msg, $condizione) {
    global $conn_string;
    
    $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");           
    $ssql = "select * from sipadx_rilasci where 1=1 ".$condizione." order by datarilascio desc";
    $result = pg_query($link, $ssql);
    $lnumrecord=pg_numrows($result);
    $counter=0;      
    $sCombo="";         
    $lastReleased="";
    if (isset($_POST["cmbRilasci"]) && $_POST["cmbRilasci"] == "") {
        $sCombo = $sCombo."Storico Rilasci Installati &nbsp;<select name=\"cmbRilasci\" onchange=\"document.DATI.prov.value=document.DATI.dest.value; document.DATI.submit();\" selected><option value=\"\">---</option>";
    }else{
        $sCombo = $sCombo."Storico Rilasci Installati &nbsp;<select name=\"cmbRilasci\" onchange=\"document.DATI.prov.value=document.DATI.dest.value; document.DATI.submit();\"><option value=\"\">---</option>";
    }
    
    if ($lnumrecord > 0) {
        while ($row = pg_fetch_array($result)) {
            if ($counter == 0) {
                $lastReleased= $row['idrilascio'];
                $sCombo = $sCombo. "<option value=\"".$row['idrilascio']."\" selected>".$row['idrilascio']."</option>";                  
            }else{
                if ($_POST["cmbRilasci"] == $row['idrilascio']) {
                    $sCombo = $sCombo. "<option value=\"".$row['idrilascio']."\" selected>".$row['idrilascio']."</option>";
                }else{
                    $sCombo = $sCombo. "<option value=\"".$row['idrilascio']."\">".$row['idrilascio']."</option>";   
                }                                     
            }
        $counter = $counter+1;
        }
    }
    pg_close($link);
    $sCombo = $sCombo."</select>";

    $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");           
    /*$ssql = " select sipadx_installazioni.codicecallcenter,";
    $ssql = $ssql . " sipadx_installazioni.idrilascio,";             
    $ssql = $ssql . " sipadx_installazioni.datainizio,";
    $ssql = $ssql . " sipadx_installazioni.datafine,";
    $ssql = $ssql . " sipadx_installazioni.errorcode,";
    $ssql = $ssql . " sipadx_installazioni.finito,";
    $ssql = $ssql . " sipadx_installazioni.flusso,";
    $ssql = $ssql . " sipadx_installazioni.log,";
    $ssql = $ssql . " tblanagraficaclienti.ragionesociale,";
    $ssql = $ssql . " tblmarca.descrizione as marca";
    $ssql = $ssql . "  from sipadx_installazioni ";
    $ssql = $ssql . "  inner join tblanagraficaclienti on";
    $ssql = $ssql . "  tblanagraficaclienti.codicecallcenter = sipadx_installazioni.codicecallcenter";
    $ssql = $ssql . "  inner join tblmarca on";
    $ssql = $ssql . "  tblanagraficaclienti.marcaid = tblmarca.marcaid";*/
    $ssql = " select sipadx_rilascixcliente.codicecallcenter,";
    $ssql = $ssql . "     sipadx_rilascixcliente.idrilascio, ";            
    $ssql = $ssql . "     sipadx_installazioni.datainizio,";
    $ssql = $ssql . "     sipadx_installazioni.datafine,";
    $ssql = $ssql . "     sipadx_installazioni.errorcode,";
    $ssql = $ssql . "     coalesce(sipadx_installazioni.finito, 0) as finito,";
    $ssql = $ssql . "     sipadx_installazioni.flusso,";
    $ssql = $ssql . "     sipadx_installazioni.log,";
    $ssql = $ssql . "     tblanagraficaclienti.ragionesociale,";
    $ssql = $ssql . "     tblmarca.descrizione as marca";
    $ssql = $ssql . "  from sipadx_rilascixcliente";
    $ssql = $ssql . "  inner join tblanagraficaclienti on";
    $ssql = $ssql . "  tblanagraficaclienti.codicecallcenter = sipadx_rilascixcliente.codicecallcenter";
    $ssql = $ssql . "  inner join tblmarca on";
    $ssql = $ssql . "  tblanagraficaclienti.marcaid = tblmarca.marcaid";
    $ssql = $ssql . " left join sipadx_logdownloads on";
    $ssql = $ssql . " sipadx_logdownloads.codicecallcenter = sipadx_rilascixcliente.codicecallcenter and ";
    $ssql = $ssql . " sipadx_logdownloads.idrilascio = sipadx_rilascixcliente.idrilascio";
    $ssql = $ssql . " left join sipadx_installazioni on";
    $ssql = $ssql . " sipadx_installazioni.codicecallcenter =  sipadx_rilascixcliente.codicecallcenter and ";
    $ssql = $ssql . " sipadx_installazioni.idrilascio =  sipadx_rilascixcliente.idrilascio"; 
    $tmpSTR = str_replace("flusso", "sipadx_rilascixcliente.flusso", $condizione);   
    if (isset($_POST["cmbRilasci"]) && ($_POST["dest"] == $_POST["prov"])) {
        if ( $_POST["cmbRilasci"] <> "") {
            $ssql = $ssql . "  where 1=1 ".$tmpSTR." and sipadx_rilascixcliente.idrilascio='".$_POST["cmbRilasci"]."'";        
        }else{
            $ssql = $ssql . "  where 1=1 ".$tmpSTR."";        
        }        
    }else{
        if ($lastReleased <> "") {
            $ssql = $ssql . "  where 1=1 ".$tmpSTR." and sipadx_rilascixcliente.idrilascio='".$lastReleased."'";    
        }else{
            $ssql = $ssql . "  where 1=1 ".$tmpSTR."";            
        }
    }
    $ssql = $ssql . " order by tblanagraficaclienti.marcaid, ragionesociale";
    //echo $ssql;
    $result = pg_query($link, $ssql);
    $lnumrecord=pg_numrows($result);
    $counter=0;
    $sOut ="";
    
    if (trim($msg) <> "") {
        $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\">";
        $sOut = $sOut. "<tr><td colspan=\"4\" class=\"tbltdcol2\">".$msg." (".$lnumrecord.") ";
        if (isset($_POST["cmbRilasci"]) && ($_POST["dest"] == $_POST["prov"])) { 
            $sOut = $sOut. "per release ".$_POST["cmbRilasci"];
        }else{
            $sOut = $sOut. "per release ".$lastReleased;
        }
        $sOut = $sOut. "</td></tr>";
        $sOut = $sOut."</table>";    
    }
    $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\" class=\"tablestyle2\">";
    $sOut = $sOut. "<tr class=\"tblheader2\"><td colspan=\"4\">".$sCombo."</td></tr>";    
    $sOut = $sOut. "<tr class=\"tblheader2\"><td>Ver. Release</td><td>Data</td><td>Cliente</td><td nowrap>Log</td></tr>";
    if ($lnumrecord > 0) {
        while ($row = pg_fetch_array($result)) {
        $log="&nbsp;";
        $cron="";   
        if (trim($row['log']) <> "") {
            /*if (trim($row["flusso"]) == "crontab") {                               
                //$cron = preg_replace('/(\d{1,2}) * * * 1-5 /sipadxcomune/import/automaticupdate/automaticUpdate.sh/i', "$1", $cron);
                $cron=nl2br(trim($row['log']));
                if (stripos($cron, "1-5 /sipadxcomune/import/automaticupdate/automaticUpdate.sh") > 0) {                                                                                                           
                    $cron="(".substr($cron, stripos($cron, "1-5 /sipadxcomune/import/")-9, 2).")";
                }else{
                    $cron = "";
                }                 
            }
            if (trim($row["flusso"]) == "crontab") {
                $tmpcron=nl2br(trim($row['log']));            
                $arrCron=explode("<br />", $tmpcron);
                foreach ($arrCron as $value) {
                    if(stripos($value, "shutdown")){
                        $cron=$value;
                    }
                }             
            }      */
            if (trim($row["flusso"]) == "sql") {
                $tmpcron=nl2br(trim($row['log']));            
                $tmpcron = str_replace("<br />", "", $tmpcron);
                $tmpcron = str_replace("idsede", "", $tmpcron);
                $cron = str_replace("-", "", $tmpcron);          
                $cron="<pre>".$cron."</pre>";
            }
            $log = "<div align=\"left\" id=\"".$row['codicecallcenter']."\" style=\" visibility: hidden; display:none; position: relative; top: -1px; margin-left: 0px; text-align: left; width: 12em; background: #fff; color: #6f704d\">".nl2br(htmlentities($row['log']))."</div><img src=\"/images/icon_settings.gif\" onclick=\"javascript:divVisibileHidden2('".$row['codicecallcenter']."')\">";
        }
        if ($row['finito'] == 0 ) {
            if (fmod($counter, 2) == 0) {             
                 $sOut = $sOut. "<tr class=\"tbltdcol1\"><td>".$row['idrilascio']."</td><td style=\"background-color: red; color: #FFFFFF;\">NON AGGIORNATO</td><td align=\"left\">".$row['marca']." - ".$row['ragionesociale']." (".$row['codicecallcenter'].")</td><td align=\"left\" nowrap>".$log."".$cron."</td></tr>";
            }else{
                 $sOut = $sOut. "<tr class=\"tbltdcol2\"><td>".$row['idrilascio']."</td><td style=\"background-color: red; color: #FFFFFF;\">NON AGGIORNATO</td><td align=\"left\">".$row['marca']." - ".$row['ragionesociale']." (".$row['codicecallcenter'].")</td><td align=\"left\" nowrap>".$log."".$cron."</td></tr>";
            } 
        
        }else{
            if (fmod($counter, 2) == 0) {             
                 $sOut = $sOut. "<tr class=\"tbltdcol1\"><td>".$row['idrilascio']."</td><td>".formatDateView($row['datafine'], "/", false)."</td><td align=\"left\">".$row['marca']." - ".$row['ragionesociale']." (".$row['codicecallcenter'].")</td><td align=\"left\" nowrap>".$log."".$cron."</td></tr>";
            }else{
                 $sOut = $sOut. "<tr class=\"tbltdcol2\"><td>".$row['idrilascio']."</td><td>".formatDateView($row['datafine'], "/", false)."</td><td align=\"left\">".$row['marca']." - ".$row['ragionesociale']." (".$row['codicecallcenter'].")</td><td align=\"left\" nowrap>".$log."".$cron."</td></tr>";
            } 
        
        } 
        $counter = $counter+1;
        }
    }else{
         $sOut = $sOut. "<tr class=\"tbltdcol1\"><td colspan=\"3\">Non esistono installazioni effettuate</td></tr>";
    }
    pg_close($link);
    $sOut = $sOut."</table>";
return $sOut;
}

function getTabManuali($msg) {
   global $conn_string, $clienteid, $marcaid, $user;   
    $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");           
    $ssql = "select * from manualistica where clienteid=".$clienteid." and marcaid=".$marcaid." and codicecallcenter='".$user."' order by path";
    $result = pg_query($link, $ssql);
    $lnumrecord=pg_numrows($result);
    $counter=0;
    $sOut ="";
    
    if (trim($msg) <> "") {
        $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\">";
        $sOut = $sOut. "<tr><td colspan=\"3\" class=\"tbltdcol2\">".$msg."</td></tr>";
        $sOut = $sOut."</table>";    
    }
    $sOut = $sOut."<table cellspacing=\"0\" cellspadding=\"0\" width=\"100%\" class=\"tablestyle2\">";
    $sOut = $sOut. "<tr class=\"tblheader2\"><td align=\"left\">Manuale</td><td>Versione</td><td>Downloads</td></tr>";
    if ($lnumrecord > 0) {
        while ($row = pg_fetch_array($result)) {
        if (fmod($counter, 2) == 0) {
             $sOut = $sOut. "<tr class=\"tbltdcol1\"><td align=\"left\">".getLinkDownManuale(803, $row['idmanuale'], $row['versione'], $row['file'], $row['nomefile'])."</td><td>".$row['versione']."</td><td>".$row['contatore']."</td></tr>";
        }else{
             $sOut = $sOut. "<tr class=\"tbltdcol2\"><td align=\"left\">".getLinkDownManuale(803, $row['idmanuale'], $row['versione'], $row['file'], $row['nomefile'])."</td><td>".$row['versione']."</td><td>".$row['contatore']."</td></tr>";
        } 
        $counter = $counter+1;
        }
    }else{
         $sOut = $sOut. "<tr class=\"tbltdcol1\"><td colspan=\"3\">Non esistono manuali disponibili al momento</td></tr>";
    }
    pg_close($link);
    $sOut = $sOut."</table>";
return $sOut;
}

function formatDateView($data, $sep, $time) {
    $dataF="";
    $dataF = substr($data, 8,2) .$sep. substr($data, 5,2) .$sep. substr($data, 0,4);
    if ($time) {
        $dataF = $dataF." " .substr($data, 11, 8);
    }

return $dataF;
}
function checkDownloads($idrelease, $numrilascio) {
    global $conn_string, $clienteid, $marcaid, $user;
    $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");           
    $ssql = "select * from downloadrilasci where clienteid=".$clienteid." and marcaid=".$marcaid." and codicecallcenter='". $user ."' and idrelease='".$idrelease."' and numrilascio=".$numrilascio."";
    $result = pg_query($link, $ssql);
    $lnumrecord=pg_numrows($result);
    $counter=0;
    if ($lnumrecord > 0) {
        $row = pg_fetch_array($result);
        $counter = $row['contatore'];
    }
    pg_close($link);
return $counter;
}             

function getLinkDownRelease($id, $vers, $numrilascio) {
    $sOut="";
    $sOut = $sOut . "<a href=\"javascript:downloads(".$id.", '".$vers."', '".$numrilascio."', '')\" class=\"marrone\">".$vers."</a>";
    return $sOut;
}
function getLinkDownManuale($id, $idmanuale, $vers, $file, $nomefile) {
    $sOut="";
    $sOut = $sOut . "<a href=\"javascript:downloads(".$id.", '".$idmanuale."', '".$vers."', '".$file."')\" class=\"marrone\">".$nomefile."</a>";
    return $sOut;
}
?>