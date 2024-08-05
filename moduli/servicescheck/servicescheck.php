<?php

switch(strtoupper($myClass->attivita)){
    case "RUNNING":
        switch(strtoupper($myClass->tipocheck)){
            case "MESSAGERELAYGENERICSERVICE":
                $sQueryServices = "SELECT servicename, ip, wsport, endpoint, tipservizio, datulcheck from tbldfnservicedetail001 where tipservizio='MESSAGERELAYGENERICSERVICE' ";  
            break;
            default:            
                $sQueryServices = "SELECT servicename, ip, wsport, endpoint, tipservizio, datulcheck from tbldfnservicedetail001 where tipservizio<>'MESSAGERELAYGENERICSERVICE' ";  
        }
        $rsSerivice = pg_query($link, $sQueryServices);
        $lNumRecordSc=pg_numrows($rsSerivice);
        if ($lNumRecordSc>0) { 
            for ($sc = 0; $sc < $lNumRecordSc; $sc++) {
                $myClassNew=new tblservappmonitoring001($conn_string);
                $myClassNew= clone $myClass;
                $sequenza=$sequenza+1;
                $myClassNew->sequenza=$sequenza;
                $rowService = pg_fetch_object($rsSerivice, $sc);
                $myClassNew->risultato="KO_WARNING";
                $myClassNew->messaggio="ERRORE SCONOSCIUTO PER :".$myClass->attivita." - ".$myClassNew->tipocheck." - ".$rowService->servicename;
                $wsurl=str_replace("#IP#", $rowService->ip, $rowService->endpoint);
                $wsurl=str_replace("#PORT#", $rowService->wsport, $wsurl);
                $risultato=array();
                //CHIAMATA AL WEB SERVICE
                $risposta=sendDataGET($wsurl);
                if (!$risposta) {                    
                    $i = 1;
                    while ($i <= 3 && !$risposta) {
                        sleep(3);
                        echo $i++;
                        $risposta=sendDataGET($wsurl);  
                    }                  
                }
                echo $risposta;
                switch(strtoupper($myClass->tipocheck)){
                    case "MESSAGERELAYGENERICSERVICE":
                        $arrRisposta=simplexml_load_string($risposta);
                        
                        if (strpos($arrRisposta[0], "Connection is open")) {
                                $risultato[0] = "OK";
                                $risultato[1] = $arrRisposta[0];                        
                        }else{
                                $risultato[0] = "KO";
                                $risultato[1] = "Nessuna risposta da ".strtoupper($myClass->tipocheck). "RISPOSTA:".$arrRisposta[0];
                        }
                    break;
                    default:
                        $bIsJson=false;
                        $arrRisposta=simplexml_load_string($risposta);
                        if (!$arrRisposta) {
                            echo "RISPOSTA NON XML";           
                            $arrRisposta=json_decode($risposta);
                                                 
                            if (!$arrRisposta) {
                                if (strpos($risposta, 'OK_') !== false) {
                                    $risultato[0] = "OK";
                                    $risultato[1] = $risposta;
                                }                                    
                            }else{
                                $tmp = array_keys(get_object_vars($arrRisposta));
                                $risposta=$arrRisposta->$tmp[0];
                                $bIsJson=true;
                                if (strpos($risposta, 'OK_') !== false) {
                                    $risultato[0] = "OK";
                                    $risultato[1] = $risposta;
                                }                                                             
                            }
                                        
                        }else{
                            echo "RISPOSTA XML";
                            $risultato=explode("_", $arrRisposta[0]);    
                        }                    
                }
                
                if ($risultato[0] == "OK") {
                    if ($bIsJson == true) {
                        $myClassNew->risultato=$risultato[1];
                    }else{
                        $myClassNew->risultato=$arrRisposta[0];    
                    }
                    
                    $myClassNew->messaggio="IL SERVIZIO ".$rowService->servicename." RISPONDE:".$risultato[1]." SU IP :".$rowService->ip." - ".$wsurl;
                }else{
                    $myClassNew->risultato="KO_WARNING";
                    $myClassNew->messaggio="ERRORE SU CHECK SERVIZIO ".$rowService->servicename." SU IP :".$rowService->ip." - ".$wsurl." RISPOSTA:".$risultato[1];                   
                }
                $arrLista[]=$myClassNew;
                $myClassNew="";
            }        
        }else{
            $myClass->risultato="OK_CHECK";
            $myClass->messaggio="Nessun servizio impostato in tbldfnservicedetail001:";
            $arrLista[]=$myClass;
        }  
        
    break;
    default:
        $myClass->risultato="KO_WARNING";
        $myClass->messaggio="ATTIVITA '".$myClass->attivita."' NON CONOSCIUTA";
        $arrLista[]=$myClass;
}    
 return $arrLista;
?>
