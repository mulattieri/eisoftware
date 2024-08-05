<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
*  FILE PER INVIO MESSAGGI A EI
*  MM 20190131
*/
require "includes/funcmonitoring.php";
//require "includes/json_formatter.php";
require "includes/Services_JSON.php";
include "classi/class.tblservappmonitoring001.php";

/*
* RECUPERO LE VARIABILI GET
*/
$SERVER_EI=$argv[1];//$_GET["SERVER_EI"];
$PATH_PROG=$argv[2];//$_GET["PATH_PROG"];
$CCC=$argv[3];//$_GET["CCC"];

/*
$SERVER_EI="web.esseitalia.eu";
$PATH_PROG="/downloads/automaticupdate/";
$CCC="3016";
*/
$SERVAPPMON="servapplogging.php";

    /*
    * LANCIO TUTTI I CHECK DA ESEGUIRE
    */
$link = getLink();
$retcode="";

    $sFile = "sam.log";

    if (!$hFileLogMain = fopen($sFile, "a")) {
        echo "Cannot open file " . $sFile . "\n";
    }
    else {            
        $bOpenLogFile = 1;
    }   

    $sequenza=0;
    $arrLista=array();
    
    if ((int)date('H') > 7) {
        $sQuery = "SELECT tblservappmonitoringconf001.flusso, tblservappmonitoringconf001.attivita, tblservappmonitoringconf001.tipocheck, tblservappmonitoringconf001.runprogram, 
                    COALESCE(tblservappmonitoringconf001.ora, 'XX') as ora,
                    COALESCE(tblservappmonitoringconf001.minuti, 'XX') as minuti,
                    COALESCE(tblservappmonitoringconf001.periodicita, 'X') as periodicita,
                    tblservappmonitoringconf001.flattivo, 
                    tblservappmonitoringconf001.idquery, tblservappmonitoringconf001.idazienda, tblservappmonitoringconf001.idsede, tblservappmonitoringconf001.idmarca, tblservappmonitoringconf001.tipomail, tblservappmonitoringconf001.extramail, 
                    coalesce(tblservappmonitoringconf001.ulesecuzione, '1900-01-01 00:00:00') as ulesecuzione, tblservappmonitoringconf001.logei, COALESCE(tbldfnquery001.commandtext,'') as commandtext 
                    FROM tblservappmonitoringconf001
                    left join tbldfnquery001 on 
                    tbldfnquery001.idquery=tblservappmonitoringconf001.idquery
                    WHERE flattivo=1 and runprogram='PHP'";
                    
        //fwrite($hFileLogMain, $sQuery);

        $rs = pg_query($link, $sQuery);
        $lNumRecord=pg_num_rows($rs);
        if ($lNumRecord>0) {
            
            for ($i = 0; $i < $lNumRecord; $i++) {
                $row = pg_fetch_object($rs, $i);
                // CONTROLLO SE IL FLUSSO HA IMPOSTAZIONI DI ESECUZIONE
                if (NeedToRun($row)) {
                    $modulo=strtolower(trim($row->flusso));
                    $sequenza=$sequenza+1;

                    $myClass=new tblservappmonitoring001($conn_string);
                    
                    $myClass->codicecallcenter=$CCC;
                    $myClass->sequenza=$sequenza;
                    
                    $myClass->flusso=trim($row->flusso);
                    $myClass->attivita=trim($row->attivita);
                    $myClass->tipocheck=trim($row->tipocheck);
                    $myClass->runprogram=trim($row->runprogram);
                    $myClass->idazienda=trim($row->idazienda);
                    $myClass->idsede=trim($row->idsede);
                    $myClass->idmarca=trim($row->idmarca);
                    $myClass->tipomail=trim($row->tipomail);
                    $myClass->extramail=trim($row->extramail);
                    $myClass->datalog=date("Y-m-d H:i:s");
                    $myClass->idquery=trim($row->idquery);
                    $myClass->commandtext=trim($row->commandtext);
                    $myClass->ulesecuzione=trim($row->ulesecuzione);
                    $myClass->logei=trim($row->logei);
                    

                    if (file_exists("moduli/".$modulo."/".$modulo.".php")) {
                        include "moduli/".$modulo."/".$modulo.".php";               
                    }else{            
                        $myClass->risultato="KO_WARNING";
                        $myClass->messaggio="MODULO :".$myClass->flusso." NON PRESENTE!!!";  
                        $arrLista[]=$myClass;                
                    }                
                    $SqlUpdate="Update tblservappmonitoringconf001 set ulesecuzione=current_timestamp where ";
                    $SqlUpdate=$SqlUpdate." flusso='".$myClass->flusso."'";
                    $SqlUpdate=$SqlUpdate." AND attivita='".$myClass->attivita."'";
                    $SqlUpdate=$SqlUpdate." AND tipocheck='".$myClass->tipocheck."'";
                    $SqlUpdate=$SqlUpdate." AND runprogram='".$myClass->runprogram."'";
                    
                    $risultato=executeQuery($link, $SqlUpdate);                            
                }           
            }
        }    
        if ($arrLista<> "") {
            for ($i=0; $i< count($arrLista); $i++) {
              $arrLista[$i]->save();
            }
        }
        
        // QUESTI DATI ARRIVANO DALLA TABELLA tblservappmonitoringconf001  dove flattivo=1 and runprogram=PHP
        
        
         /*
         * LEGGO I DATI DA MANDARE A ESSEITALIA
         */
        $link = getLink();

        $sqlPreCheck="select '".$CCC."' as codicecallcenter, tblservappmonitoring001.flusso, tblservappmonitoring001.attivita, tblservappmonitoring001.tipocheck, tblservappmonitoring001.runprogram, tblservappmonitoring001.datalog, tblservappmonitoring001.sequenza, tblservappmonitoring001.risultato, 
                   tblservappmonitoring001.messaggio, tblservappmonitoring001.flgestito, tblservappmonitoring001.tipomail, tblservappmonitoring001.extramail, tblservappmonitoring001.idazienda, tblservappmonitoring001.idsede, 
                   tblservappmonitoring001.idmarca, coalesce(tblservappmonitoringconf001.logei,0) as logei
                    from tblservappmonitoring001
                    left join tblservappmonitoringconf001 on 
                    tblservappmonitoringconf001.flusso=tblservappmonitoring001.flusso and 
                    tblservappmonitoringconf001.attivita=tblservappmonitoring001.attivita and 
                    tblservappmonitoringconf001.tipocheck=tblservappmonitoring001.tipocheck and 
                    tblservappmonitoringconf001.runprogram=tblservappmonitoring001.runprogram";
        $sqlPreCheck=$sqlPreCheck." where flgestito=0;";                    
        
        
        $rsPreCheck = pg_query($link, $sqlPreCheck);
        $lNumRecordPreCheck=pg_num_rows($rsPreCheck);
        
        if ($lNumRecordPreCheck > 100) {
            fwrite($hFileLogMain, date("Y-m-d H:i:s") . " - TROPPI RECORD DA GESTIRE!!!! ". $lNumRecordPreCheck ." BLOCCO E INVIO WARNING A ESSEITA:". "\n\n"); 
            $postvars="";
            $postvars="codicecallcenter=".$CCC."&flusso=genericcheck&attivita=BLOCCOINVIO&tipocheck=TROPPIRECORD&runprogram=PHP&datalog=".date("Y-m-d H:i:s")."&sequenza=999&risultato=KO_ERROR&messaggio=TROPPI RECORD DA GESTIRE!!!! ". $lNumRecordPreCheck ." BLOCCO E INVIO WARNING A ESSEITA&flgestito=0&tipomail=interna_monitoring&extramail=&idazienda=0&idsede=0&idmarca=0&logei=1&";
            $retcode=sendData($postvars);
            
        }else{
            $sqlCheck="select '".$CCC."' as codicecallcenter, tblservappmonitoring001.flusso, tblservappmonitoring001.attivita, tblservappmonitoring001.tipocheck, tblservappmonitoring001.runprogram, tblservappmonitoring001.datalog, tblservappmonitoring001.sequenza, tblservappmonitoring001.risultato, 
                       tblservappmonitoring001.messaggio, tblservappmonitoring001.flgestito, tblservappmonitoring001.tipomail, tblservappmonitoring001.extramail, tblservappmonitoring001.idazienda, tblservappmonitoring001.idsede, 
                       tblservappmonitoring001.idmarca, coalesce(tblservappmonitoringconf001.logei,0) as logei
                        from tblservappmonitoring001
                        left join tblservappmonitoringconf001 on 
                        tblservappmonitoringconf001.flusso=tblservappmonitoring001.flusso and 
                        tblservappmonitoringconf001.attivita=tblservappmonitoring001.attivita and 
                        tblservappmonitoringconf001.tipocheck=tblservappmonitoring001.tipocheck and 
                        tblservappmonitoringconf001.runprogram=tblservappmonitoring001.runprogram";
            $sqlCheck=$sqlCheck." where flgestito=0;";
            
            $rsSeq = getArrayRow($link, $sqlCheck);
            if ($rsSeq <>"")  {
                
                //$rowRead = pg_fetch_array($rowSeq);

                while ($rowRead = pg_fetch_array($rsSeq, null, PGSQL_ASSOC)) {            
                    $codicecallcenter=trim($rowRead["codicecallcenter"]);
                    $flusso=trim($rowRead["flusso"]);
                    $attivita=trim($rowRead["attivita"]);
                    $tipocheck=trim($rowRead["tipocheck"]);
                    $runprogram=trim($rowRead["runprogram"]);
                    $today=trim($rowRead["datalog"]);
                    $sequenza=trim($rowRead["sequenza"]);
                    $postvars="";
                    foreach($rowRead as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    } 
                    //fwrite($hFileLogMain, "POSTVARS:" .$postvars. "\n\n"); 
                    echo "POSTVARS:" .$postvars. "\n\n";
                    $retcode=sendData($postvars);
                    $arrRetcode=explode("_", $retcode);
                    
                    $strSqlUpdate="Update tblservappmonitoring001 set flgestito=".$arrRetcode[1]." where ";
                    $strSqlUpdate=$strSqlUpdate." flusso='".$flusso."'";
                    $strSqlUpdate=$strSqlUpdate." AND attivita='".$attivita."'";
                    $strSqlUpdate=$strSqlUpdate." AND tipocheck='".$tipocheck."'";
                    $strSqlUpdate=$strSqlUpdate." AND runprogram='".$runprogram."'";
                    $strSqlUpdate=$strSqlUpdate." AND datalog='".$today."'";
                    $strSqlUpdate=$strSqlUpdate." AND sequenza=".$sequenza;
                    $risultato=executeQuery($link, $strSqlUpdate);
                    
                }    
            }            
        }
        
        
              
    }else{
        fwrite($hFileLogMain, date("Y-m-d H:i:s") . " - SKIPPO PERCHE' NON SONO LE 7". "\n\n");  
    }

    fclose($hFileLogMain);
    
  
echo $retcode;

function NeedToRun($row) {
    $booEstrai = FALSE;
    $minor_timestamp="99999999999999";
    if (($row->ora == 'XX') || ($row->minuti == 'XX') || ($row->periodicita == 'X')) {
        return true;
    }else{
        $ultima_estrazione=timestamp_to_stringInt($row->ulesecuzione);//str_replace("-", "", trim($row->ulesecuzione));
        //$ultima_estrazione=str_replace(":", "", $ultima_estrazione);
        //$ultima_estrazione=str_replace(" ", "", $ultima_estrazione);
        //$ultima_estrazione=substr($ultima_estrazione, 0, 14);
        if ($ultima_estrazione=="19000101000000") {
             switch ($row->periodicita) {
                 case "H":
                 case "G":
                    $ultima_estrazione=date_to_timestampInt(dateaddInt("d",-1,date("Y-m-d")))."000000";
                    break;
                case "W":
                    $ultima_estrazione=date_to_timestampInt(dateaddInt("ww",-1,date("Y-m-d")))."000000";
                    break;
                default:
                    $ultima_estrazione=date_to_timestampInt(dateaddInt("m",-1,date("Y-m-d")))."000000";
            }
        }
        $adesso=date("Ymd");
                
        $oraElaborazione=trim($row->ora);
        $minutiElaborazione=trim($row->minuti);
        switch ($row->periodicita) {
            case "P":    //polling
                //ANCORA DA GESTIRE
                //$arpolling[]=trim($row->flusso);
                break;
            case "G":
                $tmpdate=dateaddInt("d",1,timestamp_to_dateInt($ultima_estrazione,"-"));
                $newdate=date_to_timestampInt($tmpdate);
                $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";
                break;
            case "W":
                // NON LO GESTIREI AL MOMENTO                       
                /*$tmpdate=dateaddInt("ww",1,timestamp_to_dateInt($ultima_estrazione,"-"));
                $newdate=date_to_timestampInt($tmpdate);
                $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";
                */                
                break;
            case "H":                
                $tmpOre=intval($row->ora);
                $tmpdate=dateaddInt("h",$tmpOre,$row->ulesecuzione);
                $prossima_estrazione=timestamp_to_stringInt($tmpdate);
                $oraElaborazione=getOreMinutiInt($prossima_estrazione, 'H');
                $minutiElaborazione=getOreMinutiInt($prossima_estrazione, 'i');
                
                break;
            case "F":
                // NON LO GESTIREI AL MOMENTO                       
                break;                    
            default:
                $tmpdate=substr($ultima_estrazione,0,6)."01";
                $newdate=date_to_timestampInt(dateaddInt("m",1,timestamp_to_dateInt($tmpdate,"-")));
                $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";
        }
                
        $adesso=date("YmdHis");
        if (($row->periodicita!="P")&&($adesso>$prossima_estrazione)) {    
            $booEstrai = FALSE;
            $adessoArr=getdate();
            $adesso_hr=$adessoArr['hours'];
            $adesso_min=$adessoArr['minutes'];
            if ($adesso_hr>$oraElaborazione) {                    
                $booEstrai = TRUE;                                
            }else{
                if ($adesso_hr == $oraElaborazione) {
                    if ($adesso_min>$minutiElaborazione) {
                        $booEstrai = TRUE;
                    }
                }
            }
/*            if ($booEstrai==TRUE) {
                //annullo estrazione se viene specificato un giorno della settimana e non siamo in quel giorno
                if ($row->periodicita=="W" and $row->giorno>=0) {
                    if ($adessoArr['wday']<>$row->giorno) {
                        $booEstrai = FALSE;
                    }
                }
            }
*/            
            if ($booEstrai==TRUE) {
                if ($prossima_estrazione<$minor_timestamp) {    //devo capire qual'� il pi� vecchio
                    $minor_timestamp=$prossima_estrazione;
                    $flusso_da_elaborare=$row->flusso;
                }
            }
        }        
    }
    if ($booEstrai) {
        return true;
    }
}

function dateaddInt($interval,$number,$dateTime) {
    #$newdate = dateaddInt("d",3,"2006-12-12");    #  add 3 days to date
    #$newdate = dateaddInt("s",3,"2006-12-12");    #  add 3 seconds to date
    #$newdate = dateaddInt("m",3,"2006-12-12");    #  add 3 minutes to date
    #$newdate = dateaddInt("h",3,"2006-12-12");    #  add 3 hours to date
    #$newdate = dateaddInt("ww",3,"2006-12-12");    #  add 3 weeks days to date
    #$newdate = dateaddInt("m",3,"2006-12-12");    #  add 3 months to date
    #$newdate = dateaddInt("yyyy",3,"2006-12-12");    #  add 3 years to date
    #$newdate = dateaddInt("d",-3,"2006-12-12");    #  subtract 3 days from date


    $dateTime = (strtotime($dateTime) != -1) ? strtotime($dateTime) : $dateTime;
    $dateTimeArr=getdate($dateTime);

    $yr=$dateTimeArr['year'];
    $mon=$dateTimeArr['mon'];
    $day=$dateTimeArr['mday'];
    $hr=$dateTimeArr['hours'];
    $min=$dateTimeArr['minutes'];
    $sec=$dateTimeArr['seconds'];

    switch($interval) {
        case "s"://seconds
            $sec += $number;
            break;

        case "n"://minutes
            $min += $number;
            break;

        case "h"://hours
            $hr += $number;
            break;

        case "d"://days
            $day += $number;
            break;

        case "ww"://Week
            $day += ($number * 7);
            break;

        case "m": //similar result "m" dateDiff Microsoft
            $mon += $number;
            break;

        case "yyyy": //similar result "yyyy" dateDiff Microsoft
            $yr += $number;
            break;

        default:
            $day += $number;
         }

        $dateTime = mktime($hr,$min,$sec,$mon,$day,$yr);
        $dateTimeArr=getdate($dateTime);

        $nosecmin = 0;
        $min=$dateTimeArr['minutes'];
        $sec=$dateTimeArr['seconds'];

        if ($hr==0){$nosecmin += 1;}
        if ($min==0){$nosecmin += 1;}
        if ($sec==0){$nosecmin += 1;}

        if ($nosecmin>2){     return(date("Y-m-d",$dateTime));} else {     return(date("Y-m-d G:i:s",$dateTime));}
}
function timestamp_to_dateInt($ts,$sep){
$out=substr($ts,0,4).$sep.substr($ts,4,2).$sep.substr($ts,6,2);
return $out;
}
function date_to_timestampInt($td){
$out=substr($td,0,4).substr($td,5,2).substr($td,8,2);
return $out;
}
function timestamp_to_stringInt($ts) {
    $out=substr($ts,0,4).substr($ts,5,2).substr($ts,8,2).substr($ts,11,2).substr($ts,14,2).substr($ts,17,2);
    return $out;   
}
function getOreMinutiInt($ts, $tipo) {
    switch($tipo) {
        case "H":
            $out=substr($ts,8,2);                
        break;
        case "i":
            $out=substr($ts,10,2);
        break;
    }
    return $out;       
}

?>
