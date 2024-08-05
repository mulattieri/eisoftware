<?php
$myClass->risultato="KO_WARNING";
$myClass->messaggio="ERRORE SCONOSCIUTO PER :".$myClass->attivita." - ".$myClass->tipocheck;  
switch(strtoupper($myClass->attivita)){
    case "CHECKIDQUERY":
    case "CHECKIDQUERYNORESULT":
        switch(strtoupper($myClass->tipocheck)){
            case "XXXXXX":
                /*SE SERVISSE IMPLEMENTARE UNA LOGICA DIVERSA UTILIZZIAMO QUESTO CAMPO ALTRIMENTI MI ASPETTO CHE CONTENGA IL NOME DEL CHECK CHE STIAMO EFFETTUANDO*/
            break;
            default:
                if (trim($myClass->commandtext) <> "") {
                    $rsCheck = pg_query($link, $myClass->commandtext);
                    $lNumRecordCheck=pg_numrows($rsCheck);
                    if ($lNumRecordCheck>0) {
                        $bFirstRecord=true;       
                        while ($rowRead = pg_fetch_array($rsCheck, null, PGSQL_ASSOC)) {
                            if (array_key_exists("risultato", $rowRead)) {
                                switch (substr(trim($rowRead["risultato"]), 0, 3)) {
                                    case "OK_":
                                    case "KO_":
                                        $myClass->risultato= trim($rowRead["risultato"]);
                                        if ($bFirstRecord) {
                                            $myClass->messaggio=trim($rowRead["messaggio"]); 
                                            $bFirstRecord = false;                                     
                                        }else{
                                            $myClass->messaggio=$myClass->messaggio."\n".trim($rowRead["messaggio"]);
                                        }                                        
                                    break;
                                    default:
                                        $myClass->risultato="KO_WARNING";
                                        $myClass->messaggio="LA COLONNA 'risultato' nell' ID QUERY:".$myClass->idquery." - non comincia con OK_ oppure KO_";
                                }
                            
                            }else{
                                $myClass->risultato="KO_WARNING";
                                $myClass->messaggio="MANCA LA COLONNA 'risultato' nell' ID QUERY:".$myClass->idquery;
                                
                            }
                            
                        }
                    }else{
                        switch (strtoupper($myClass->attivita)) {
                            case "CHECKIDQUERYNORESULT":
                                $myClass->risultato="OK_CHECK";
                                $myClass->messaggio="Nessun record per idquery:".$myClass->idquery;                                    
                            break;
                            default:
                                $myClass->risultato="KO_WARNING";
                                $myClass->messaggio="RESULTSET VUOTO:".$myClass->commandtext;                                    

                        }
                    }
                }else{
                    $myClass->risultato="KO_ERROR";
                    $myClass->messaggio="MANCA COMMAND TEXT X ID QUERY:".$myClass->idquery;
                }                                           
        }        
    break;
    default:
        $myClass->risultato="KO_WARNING";
        $myClass->messaggio="ATTIVITA '".$myClass->attivita."' NON CONOSCIUTA";            
}
    
$arrLista[]=$myClass;

return $arrLista;
?>