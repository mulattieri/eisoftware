<?php
$giornoSettimana=strtoupper(date("D"));
switch(strtoupper($myClass->attivita)){
    case "RUNNING":
        $sQueryCron = "select flusso, ora, minuti, periodicita, flattivo, coalesce(ulesecuzione,'') as ulesecuzione, sys_datcreazione, 
                            sys_idazcreazione, sys_idutcreazione, sys_datulmodifica, sys_idutulmodifica, 
                            cpccchk, coalesce(inesecuzione,'') as inesecuzione, coalesce(finesecuzione,'') as finesecuzione, giorno, applicationpath, 
                            applicationarguments, flaction, category, subcategory, exitmessage, 
                            elorari, elgiornisett from tbldfncron001 
                        where coalesce(flattivo, 'N') <> 'N' order by flattivo, periodicita, flusso";  
        $rsCron = pg_query($link, $sQueryCron);
        $lNumRecordCron=pg_numrows($rsCron);
        if ($lNumRecordCron>0) { 
            for ($cron = 0; $cron < $lNumRecordCron; $cron++) {
                // CHECK SE STA GIRANDO
                $rowCron = pg_fetch_object($rsCron, $cron);
                $ultima_estrazione=trim($rowCron->ulesecuzione);
                $adesso=date("YmdHis");
                $booEccezione=false;
                if ($ultima_estrazione == "") {
                    // ERRORE FLUSSO DEVE PARTIRE
                    $myClassNew=new tblservappmonitoring001($conn_string);
                    $myClassNew= clone $myClass;
                    $sequenza=$sequenza+1;
                    $myClassNew->sequenza=$sequenza;                    
                    $myClassNew->risultato="KO_WARNING";
                    $myClassNew->messaggio="ERRORE SCONOSCIUTO PER :".$myClass->attivita." - ".$myClassNew->tipocheck." - ".$rowCron->servicename;                
                    $arrLista[]=$myClassNew;
                    $myClassNew="";
                    
                }else{
                    switch (strtoupper($rowCron->periodicita)) {
                        case "F";
                            // Questo tipo di esecuzione prevede una data/ora specifica 
                            // E' necessario andare in JOIN sulla tabella tbldfncron_extended001 con il flusso
                                    
                            $sCheck="Select * from tbldfncron_extended001";
                            $sCheck=$sCheck." where flusso='".trim($rowCron->flusso)."' ";
                            $sCheck=$sCheck." and coalesce(fleseguito, '')  = ''";
                            $sCheck=$sCheck." and coalesce(flattivo, '')  = 'S'";
                            $sCheck=$sCheck." and coalesce(inesecuzione, '') = ''";
                            $sCheck=$sCheck." and (";
                            $sCheck=$sCheck." (to_date(datesecuzione, 'yyyy-mm-DD') <= to_date(current_timestamp, 'yyyy-mm-DD') ) and";
                            $sCheck=$sCheck." ((to_date(datesecuzione, 'yyyy-mm-DD') < to_date(current_timestamp, 'yyyy-mm-DD')) or ((oraesecuzione < trim(to_char(extract(hour from current_timestamp), '00'))) or";
                            $sCheck=$sCheck." (oraesecuzione = trim(to_char(extract(hour from current_timestamp), '00')) and minutiesecuzione <= trim(to_char(extract(minute from current_timestamp), '00')))))";
                            $sCheck=$sCheck." )";
                            $sCheck=$sCheck." order by datesecuzione, oraesecuzione, minutiesecuzione limit 1";                          
                            
                            $rsCheck = pg_query($link, $sCheck);
                            $lNumRecordCheck=pg_numrows($rsCheck);
                            if ($lNumRecordCheck>0) {    
                                $rowCheck = pg_fetch_object($rsCheck);
                                $oraElaborazione=trim($rowCheck->oraesecuzione);
                                $minutiElaborazione=trim($rowCheck->minutiesecuzione);                                               
                                $newdate=date_to_timestamp($rowCheck->datesecuzione);
                                $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";
                                $now_diff=string_to_timestamp($adesso, "-");
                                $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                                $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                                if ($giornoSettimana == "MON") {
                                    if ($differenza > 78) {
                                        $booEccezione=true;                                    
                                    }                                                                        
                                }else{
                                    if ($differenza > 26) {
                                        $booEccezione=true;                                    
                                    }                                    
                                }
                            }                            
                        break;
                        case "G";
                            $tmpdate=dateadd("d",1,timestamp_to_date($ultima_estrazione,"-"));
                            $newdate=date_to_timestamp($tmpdate);
                            $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";   
                            $now_diff=string_to_timestamp($adesso, "-");
                            $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                            $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                            if ($giornoSettimana == "MON") {
                                if ($differenza > 78) {
                                    $booEccezione=true;                                    
                                }                                                                                                             
                            }else{
                                if ($differenza > 26) {
                                    $booEccezione=true;                                    
                                }                                                                             
                            }
                            
                        break;
                        case "W";
                            $tmpdate=dateadd("ww",1,timestamp_to_date($ultima_estrazione,"-"));
                            $newdate=date_to_timestamp($tmpdate);
                            $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";                    
                            $now_diff=string_to_timestamp($adesso, "-");
                            $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                            $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                            if ($giornoSettimana == "MON") {
                                if ($differenza > 222) {
                                    $booEccezione=true;                                    
                                }                                                                                                                 
                            }else{
                                if ($differenza > 170) {
                                    $booEccezione=true;                                    
                                }                                                                             
                            }


                        break;
                        case "M";
                            $tmpdate=substr($ultima_estrazione,0,6)."01";
                            $newdate=date_to_timestamp(dateadd("m",1,timestamp_to_date($tmpdate,"-")));
                            $prossima_estrazione=$newdate.$oraElaborazione.$minutiElaborazione."00";
                            $now_diff=string_to_timestamp($adesso, "-");
                            $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                            $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                            if ($giornoSettimana == "MON") {
                                if ($differenza > 802) {
                                    $booEccezione=true;                                    
                                }                                             
                                
                            }else{
                                if ($differenza > 750) {
                                    $booEccezione=true;                                    
                                }                                                                             
                            }

                        break;
                        case "N";
                            //MINUTI
                            $now_diff=string_to_timestamp($adesso, "-");
                            $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                            $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                            if ($giornoSettimana == "MON") {
                                if ($differenza > 53) {
                                $booEccezione=true;                                    
                                }                                                                             
                                
                            }else{
                                if ($differenza > 1) {
                                $booEccezione=true;                                    
                                }                                                                             
                            }

                        
                        break; 
                        case "P";                       
                            $now_diff=string_to_timestamp($adesso, "-");
                            $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                            $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                            if ($giornoSettimana == "MON") {
                                if ($differenza > 53) {
                                $booEccezione=true;                                    
                                }                                                                             
                                
                            }else{
                                if ($differenza > 1) {
                                $booEccezione=true;                                    
                                }                                                                             
                            }                                          
                            
                        break;                                       
                        case "H";
                            // ORA
                            $prossima_estrazione=GetNextTime(trim($rowCron->elorari),$ultima_estrazione);
                            $now_diff=string_to_timestamp($adesso, "-");
                            $ultima_diff = string_to_timestamp($ultima_estrazione, "-");
                            $differenza=((strtotime($now_diff) - strtotime($ultima_diff)) / 3600);
                            if ($giornoSettimana == "MON") {
                                if ($differenza > 52) {
                                    $booEccezione=true;                                    
                                }                                                                                                             
                            }else{
                                if ($differenza > 26) {
                                    $booEccezione=true;                                    
                                }                                                                             
                            }                               
                            
                        break;                        
                    } // FINE SWITCH
                    if ($booEccezione==TRUE) {
                        //  SE SONO PASSATI TOT MINUTI MANDO ERRORE
                        $myClassNew=new tblservappmonitoring001($conn_string);
                        $myClassNew= clone $myClass;
                        $sequenza=$sequenza+1;
                        $myClassNew->sequenza=$sequenza;                    
                        $myClassNew->risultato="KO_WARNING";
                        $myClassNew->messaggio="ERRORE FLUSSO :".$rowCron->flusso . " - ULTIMA ESECUZIONE:".$rowCron->ultimaesecuzione." - ORE PASSATE:".$differenza." - " . $myClass->attivita." - ".$myClassNew->tipocheck." - ".$rowCron->flusso;                
                        $arrLista[]=$myClassNew;
                        $myClassNew="";                            
                    }                    
                    /*if (($adesso>$prossima_estrazione)) {    
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
                        if ($booEstrai==TRUE) {
                            //annullo estrazione se viene specificato un giorno della settimana e non siamo in quel giorno
                            if ($rowCron->periodicita=="W" and $rowCron->giorno>=0) {
                                if ($adessoArr['wday']<>$rowCron->giorno) {
                                    $booEstrai = FALSE;
                                }
                            }
                        }
                        */

                    //}                    
                }// FINE ELSE ULTIMA ESECUZIONE                
            }        
        }else{
            $myClass->risultato="OK_CHECK";
            $myClass->messaggio="Nessun flusso impostato in tbldfncron001:";
            $arrLista[]=$myClass;
        }  
        
    break;
    default:
        $myClass->risultato="KO_WARNING";
        $myClass->messaggio="ATTIVITA '".$myClass->attivita."' NON CONOSCIUTA";
        $arrLista[]=$myClass;
}    
 return $arrLista;
 
function dateAdd($interval,$number,$dateTime) {
    #$newdate = dateadd("d",3,"2006-12-12");    #  add 3 days to date
    #$newdate = dateadd("s",3,"2006-12-12");    #  add 3 seconds to date
    #$newdate = dateadd("m",3,"2006-12-12");    #  add 3 minutes to date
    #$newdate = dateadd("h",3,"2006-12-12");    #  add 3 hours to date
    #$newdate = dateadd("ww",3,"2006-12-12");    #  add 3 weeks days to date
    #$newdate = dateadd("m",3,"2006-12-12");    #  add 3 months to date
    #$newdate = dateadd("yyyy",3,"2006-12-12");    #  add 3 years to date
    #$newdate = dateadd("d",-3,"2006-12-12");    #  subtract 3 days from date


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

function timestamp_to_date($ts,$sep){
$out=substr($ts,0,4).$sep.substr($ts,4,2).$sep.substr($ts,6,2);
return $out;
}
function string_to_timestamp($ts,$sep) {
$out=substr($ts,0,4).$sep.substr($ts,4,2).$sep.substr($ts,6,2)." ".substr($ts,8,2).":".substr($ts,10,2).":".substr($ts,12,2);
return $out;
    
}
function date_to_timestamp($td){
$out=substr($td,0,4).substr($td,5,2).substr($td,8,2);
return $out;
}

function checkScriptRunning($modulo) {
    global $conn_string, $Gruppo; 
    $now=date("YmdHis");
    $now_diff=timestamp_to_date($now, "-");
    $link = pg_connect ($conn_string);  
    if(!$link) {
        ScriviLogMain($hFileLogMain, "Connection failed to " . $DbName);   
        return -1;
    }
    $sQuery = "SELECT inesecuzione, finesecuzione from tbldfncron".$Gruppo;
    $sQuery .=" WHERE flusso='".strtoupper($modulo)."'";
    $rs = pg_query($link, $sQuery);
    $lNumRecord=pg_numrows($rs);
    if ($lNumRecord>0) {
        $row = pg_fetch_object($rs);
        $inizio_esecuzione=trim($row->inesecuzione);
        $fine_esecuzione=trim($row->finesecuzione);
        $inizio_diff = timestamp_to_date($inizio_esecuzione, "-");
        if (($fine_esecuzione <> "") || ($inizio_esecuzione == "")) {
            /*L'ultima volta che esegui.php è stata lanciata ha finito la sua esecuzione
            //Oppure E' la prima volta che lancio lo script
            // Posso rieseguire lo script
            */
            $sQuery = "UPDATE tbldfncron".$Gruppo;
            $sQuery .=" SET inesecuzione='".$now."', ";
            $sQuery .=" finesecuzione=NULL ";
            $sQuery .=" ,cpccchk='ex_cr' ";
            $sQuery .=" WHERE flusso='".strtoupper($modulo)."'";
            $result = pg_query($link,$sQuery);
            if (!$result){
                return -3;
            }            
            return 1;
        }else{
            /*Lo script che è stato lanciato prima di questo non è andato a buon fine
            //Oppure sta ancora girando
            // Verifico se la differenza tra oggi e l'ultima inesecuzione è maggiore o uguale a 1 giorno
            */
            $differenza=strtotime($now_diff) - strtotime($inizio_diff);
            if ($differenza >= 86400) {
                //Posso rieseguire lo script 
                $sQuery = "UPDATE tbldfncron".$Gruppo;
                $sQuery .=" SET inesecuzione='".$now."', ";
                $sQuery .=" finesecuzione=NULL ";
                $sQuery .=" ,cpccchk='ex_cr2' ";
                $sQuery .=" WHERE flusso='".strtoupper($modulo)."'";
                $result = pg_query($link,$sQuery);
                if (!$result){
                    return -3;
                }                  
              return 1;
            }else{
                //Script in eseguzione
              return -4;
            }
        }
    }else{
        //ERRORE NEL RECUPERO RECORD
        return -2;
    }
}

function GetNextTime($strelencoorari,$ulesecuzione){
    global $hFileLogMain;
    $primoorario="";
    $arorari=explode(";",$strelencoorari);
    if (count($arorari)>0){
        foreach ($arorari as $orario){
            $orario=str_replace(":", "", $orario);
            if (strlen($orario)==4){
                if ($primoorario=="") $primoorario=$orario; 
                $tmpdatetime=date("Ymd").$orario."00";
                if ($ulesecuzione<=$tmpdatetime) {
                    return $tmpdatetime;
                }
            }else{
                //ScriviLogMain($hFileLogMain, "Error in cron definition type H  time: " . $orario);
            }
        }
    }else{
        //ScriviLogMain($hFileLogMain, "Error in cron definition type H : " . $strelencoorari);
    }
    if ($primoorario!=""){
        //se non ho trovato un orario valido prendo come prossimo il primo di domani 
        $tmpdatetime=date("Ymd",dateadd("d",1,date())).$primoorario."00";
        return $tmpdatetime;
    }
    //se non ho trovato nulla il flusso è configurato male
    return "20990101235959";
}
 
?>
