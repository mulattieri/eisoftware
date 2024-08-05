<?php
require "config.php";
require "function.php";
require "ccurl.php";

//require "mycurl.class.php";
/*Variabili per la connessione al database*/


/*
$strServer = "10.100.103.32";
$DbName = "novelli20180906";
$UId = "postgres";
$Pw = "";
$portaDB = "5432";
*/
$conn_string = "host=".$strServer." port=" . $portaDB  . " dbname=" .$DbName." user=" .$UId." password=" .$Pw."";

function getLink() {
    global $conn_string;
        $link = pg_connect($conn_string) or die("immpossibile stabilire una connessione!");                   
    return $link;
}
function closeLink($link) {
    pg_close($link);
}
function getRow($link, $strSql) {
    $row="";
    $result = pg_query($link, $strSql);
    $lnumrecord=pg_num_rows($result);
    $counter=0;
    if ($lnumrecord > 0) {
        $row = pg_fetch_array($result);
    }
    return $row;
}

function getArrayRow($link, $strSql) {
    $row="";
    $result = pg_query($link, $strSql);
    $lnumrecord=pg_num_rows($result);
    if ($lnumrecord < 1) {
        $result = "";
    }
    return $result;
}

function executeQuery($link, $strSql) {
    $result = pg_query($link, $strSql);
    if ($result ) {
        return true;
    }else{
        return false;
    }   
}

function sendData($postFields) {
    global $SERVER_EI, $PATH_PROG, $SERVAPPMON;
    
      $url="http://" . $SERVER_EI . $PATH_PROG . $SERVAPPMON;
      echo $url;
      $sendLog=new mycurl($url, true, 60);
      $sendLog->setPost($postFields);
      $sendLog->createCurl($url);
      return $sendLog->_webpage;  
}  

function sendDataGET($url) {
      $sendLog=new mycurl($url, true, 60);
      $sendLog->createCurl($url);
      return $sendLog->_webpage;  
    
}
function dbquery($tsdb, $query) {
    $logtime=true;
    $mtime = microtime();
      $mtime = explode(" ",$mtime);
      $mtime = $mtime[1] + $mtime[0];
      $starttime = $mtime; 
  
        if (trim($query)!=""){
            $rs = pg_query($tsdb, $query);
            if ($logtime){
                $mtime = microtime();
                   $mtime = explode(" ",$mtime);
                   $mtime = $mtime[1] + $mtime[0];
                   $endtime = $mtime;
                   $totaltime = ($endtime - $starttime);
                   $out="Tempo di esecuzione: ".$totaltime." seconds";
                   if ($totaltime>5)
                        echo "Query troppo lenta: ".$query."\n".$out;
            }
            return $rs;
        }else{
            return null;
        } 
        //if (!$rs) fdebug("Errore nelal query:".$query);
        
    }  
?>