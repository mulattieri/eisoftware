<?php
$myClass->risultato="KO_WARNING";
$myClass->messaggio="ERRORE SCONOSCIUTO PER :".$myClass->attivita." - ".$myClass->tipocheck;  
switch(strtoupper($myClass->attivita)){
    case "CHECKRUNNING":
        switch(strtoupper($myClass->tipocheck)){
            case "RUNNING":
                if (trim($myClass->commandtext) <> "") {
                    $rsCheck = pg_query($link, $myClass->commandtext);
                    $lNumRecordCheck=pg_numrows($rsCheck);
                    if ($lNumRecordCheck>0) {
                        $bFirstRecord=true;       
                        while ($rowRead = pg_fetch_array($rsCheck, null, PGSQL_ASSOC)) {                       
                            $myClass->risultato= trim($rowRead["risultato"]);
                            if ($bFirstRecord) {
                                $myClass->messaggio=trim($rowRead["messaggio"]);   
                                $bFirstRecord=false;                                   
                            }else{
                                $myClass->messaggio=$myClass->messaggio."\n".trim($rowRead["messaggio"]);
                            }
                        }
                    }else{
                        $myClass->risultato="KO_WARNING";
                        $myClass->messaggio="RESULTSET VUOTO:".$myClass->commandtext;                                    
                    }
                }else{
                    $myClass->risultato="KO_ERROR";
                    $myClass->messaggio="MANCA COMMAND TEXT X ID QUERY:".$myClass->idquery;
                }               
            break;
            default:
                $myClass->risultato="KO_WARNING";
                $myClass->messaggio="TIPO CHECK '".$myClass->tipocheck."' NON CONOSCIUTO";            
        }        
    break;
    default:
        $myClass->risultato="KO_WARNING";
        $myClass->messaggio="ATTIVITA '".$myClass->attivita."' NON CONOSCIUTA";            
}
    
$arrLista[]=$myClass;

return $arrLista;
?>