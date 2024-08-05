<?php
function get_otherfiles($ext_filter=""){
    global $link_conf;
    $lsql = "SELECT * FROM tbldfnotherfiles001";
    // Se il filtro estensione è specificato, lo aggiungiamo al SQL
    if ($ext_filter) {
        // Non usiamo $ext_filter nella query in questo esempio
    }
    // Esecuzione della query
    $out = array();
    $rs = pg_query($link_conf, $lsql);
    while ($row = pg_fetch_assoc($rs)) {
        $out[] = [
            $row["nomprogramma"],
            $row['idmajor'].".".$row['idminor'].".".$row['idrevision'].".".$row['idsubrevision'],
            $row["md5"],
            $row["pacrilascio"],
            $row["datprogramma"]
        ];
    }
    return $out;
}

function checkfile(){
    $filename = CHECK_FILE;
    $arout = array();
    if (file_exists($filename) && filesize($filename) > 0) {
        $contents = file_get_contents($filename);
        $artmp = preg_split("/\n/", $contents);
        foreach ($artmp as $line) {
            if (!empty($line)) {
                $arriga = preg_split("/\|/", $line);
                $arout[] = [$arriga[0], $arriga[1], $arriga[2]];
            }
        }
    }
    return $arout;
}

function checkfile_other(){
    $filename = CHECK_FILE_OTHER;
    $arout = array();
    if (file_exists($filename) && filesize($filename) > 0) {
        $contents = file_get_contents($filename);
        $artmp = preg_split("/\n/", $contents);
        foreach ($artmp as $line) {
            if (!empty($line)) {
                $arriga = preg_split("/\|/", $line);
                $arout[] = $arriga;
            }
        }
    }
    return $arout;
}

function getallip(){
    global $link;
    $lsql = "SELECT DISTINCT ipaddress FROM ei_agclient WHERE COALESCE(ipaddress, '') <> '' ORDER BY ipaddress";
    $rs = pg_query($link, $lsql);
    $out = array();
    while ($row = pg_fetch_assoc($rs)) {
        $out[] = $row['ipaddress'];
    }
    return $out;
}

function getalldb(&$dbdefault){
    global $link_conf;
    $lsql = "SELECT pg_database.datname AS nomedb FROM pg_database";
    $rs = pg_query($link_conf, $lsql);
    $strout = "";
    $boofound = false;
    $dbvalido = '';
    while ($row = pg_fetch_assoc($rs)) {
        if (preg_match("/^sipadx_conf|^integra|^template|^default/", $row['nomedb'])) {
            // Skip this db
        } else {
            $strout .= "<option value='".$row['nomedb']."' ";
            if ($row['nomedb'] == $dbdefault) {
                $strout .= "selected";
                $boofound = true;
            }
            $strout .= ">".$row['nomedb']."</option>\n";
            if (preg_match("/^sipadx/", $row['nomedb'])) {
                $dbvalido = $row['nomedb'];
            }
        }
    }
    if (!$boofound) {
        $dbdefault = $dbvalido;
    }
    return $strout;
}

function getclientversion($ip){
    global $link;
    $lsql = "SELECT * FROM ei_agclient WHERE ipaddress='".$ip."'";
    $rs = pg_query($link, $lsql);
    $out = array();
    while ($row = pg_fetch_assoc($rs)) {
        $out[] = trim($row['updnote']);
    }
    return $out;
}

function getversion($file, $strmd5){
    global $link_conf;
    $lsql = "SELECT idmajor, idminor, idrevision, idsubrevision FROM tbldfnsoftware001 WHERE nomprogramma='".$file."'";
    if ($strmd5 != "") {
        $lsql .= " AND md5='".$strmd5."'";
    } else {
        $lsql .= " ORDER BY datprogramma DESC LIMIT 1";
    }
    $rs = pg_query($link_conf, $lsql);
    $out = "";
    while ($row = pg_fetch_assoc($rs)) {
        $out = $row['idmajor'].".".$row['idminor'].".".$row['idrevision'].".".$row['idsubrevision'];
    }
    return $out;
}

function get_dbversion($file, $strmd5, $sub_path, $ext_filter){
    global $link_conf;
    $lsql = "SELECT idmajor, idminor, idrevision, idsubrevision FROM tbldfnotherfiles001 WHERE nomprogramma='".$file."'";
    if ($strmd5 != "") {
        $lsql .= " AND md5='".$strmd5."'";
    } else {
        $lsql .= " ORDER BY datprogramma DESC LIMIT 1";
    }
    $rs = pg_query($link_conf, $lsql);
    $out = "";
    while ($row = pg_fetch_assoc($rs)) {
        $out = $row['idmajor'].".".$row['idminor'].".".$row['idrevision'].".".$row['idsubrevision'];
    }
    return $out;
}

function getdata($file, $strmd5){
    global $link_conf;
    $lsql = "SELECT pacrilascio FROM tbldfnsoftware001 WHERE nomprogramma='".$file."'";
    if ($strmd5 != "") {
        $lsql .= " AND md5='".$strmd5."'";
    } else {
        $lsql .= " ORDER BY datprogramma DESC LIMIT 1";
    }
    $rs = pg_query($link_conf, $lsql);
    $out = "";
    while ($row = pg_fetch_assoc($rs)) {
        $out = $row['pacrilascio'];
    }
    return $out;
}

function updatetable($lsql){
    global $link_conf;
    pg_query($link_conf, $lsql);
}

function checkdir($caller=""){
    $arfile = array();
    $pathtocheck = LASTVERSION;
    if ($handle = opendir($pathtocheck)) {
        while (false !== ($file = readdir($handle))) {
            if ($caller == "index.php") {
                if (preg_match("/\.dll$|\.exe$/", $file)) {
                    $strmd5 = md5_file($pathtocheck."/".$file);
                    $arfile[] = array($file, getversion($file, $strmd5), $strmd5, getdata($file, $strmd5));
                }
            } else {
                if (preg_match("/\.dll$|\.exe$/", $file)) {
                    $strmd5 = md5_file($pathtocheck."/".$file);
                    $arfile[] = array($file, getversion($file, $strmd5), $strmd5, getdata($file, $strmd5));
                }
            }
        }
        closedir($handle);
    }
    return $arfile;
}

function checkdir_other($sub_path, $ext_filter){
    $arfile = array();
    $pathtocheck = LASTVERSION."/".$sub_path;
    if ($handle = opendir($pathtocheck)) {
        while (false !== ($file = readdir($handle))) {
            $eregfilter = "/\.".$ext_filter."$/";
            if (preg_match($eregfilter, $file)) {
                $strmd5 = md5_file($pathtocheck."/".$file);
                $arfile[] = array($file, get_dbversion($file, $strmd5, $sub_path, $ext_filter), $strmd5, getdata($file, $strmd5));
            }
        }
        closedir($handle);
    }
    return $arfile;
}

function ComparaVersioni($strVersioneLocale, $strVersioneRemota){
    $arLoc = preg_split("/\./", $strVersioneLocale);
    $arRem = preg_split("/\./", $strVersioneRemota);
    
    $arLoc = array_pad($arLoc, 4, "0");
    $arRem = array_pad($arRem, 4, "0");

    for ($i = 0; $i < 4; $i++) {
        if (intval($arLoc[$i]) > intval($arRem[$i])) {
            return ">";
        } elseif (intval($arLoc[$i]) < intval($arRem[$i])) {
            return "<";
        }
    }
    return "=";
}

function f_numeric($dato){
    if (is_numeric($dato)) {
        if (strpos($dato, ".") !== false || strpos($dato, ",") !== false) {
            return f_formatprice($dato);
        }
    }
    return $dato;
}

function isDate($i_sDate){
    $i_sDate = substr($i_sDate, 0, 10);
    if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $i_sDate)) {
        return false;
    }
    $arrDate = explode("-", $i_sDate);
    $intDay = intval($arrDate[2]);
    $intMonth = intval($arrDate[1]);
    $intYear = $arrDate[0];
    return checkdate($intMonth, $intDay, $intYear);
}
?>
