<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include "includes/global.php";   
include "includes/function.php";   

/*---------------------------------------------------------------------------
	Verifica eseguibili e dll
-----------------------------------------------------------------------------	*/
$arattuali=checkdir();		/* scorre i file della lastversion */
$arnuove=checkfile();		/* scorre i file descritti nel 0000reg-software.txt*/
$messaggi[]="";
echo "Nuove: ".count($arnuove)." Nella lastversion: ".count($arattuali)."\n";
for ($i=0;$i<count($arnuove);$i++){
	$boofound=false;
	for ($j=0;$j<count($arattuali);$j++){
		if (trim($arattuali[$j][0])==trim($arnuove[$i][0])){
			//stesso nome
			$boofound=true;
			switch (ComparaVersioni($arnuove[$i][1],$arattuali[$j][1])){
				case ">":
					//richiesto aggiornamento
					$lsql=$arnuove[$i][2];
					updatetable($arnuove[$i][2]);
					copy(CHECK_PATH."/".trim($arnuove[$i][0]),LASTVERSION."/".trim($arnuove[$i][0]));
					$messaggi[]="Aggiorno: ".$arnuove[$i][0]." versione ".$arnuove[$i][1];
					//Caso file zippati
					if (file_exists(CHECK_PATH."/zip/zip".trim($arnuove[$i][0]))){
						copy(CHECK_PATH."/zip/zip".trim($arnuove[$i][0]),LASTVERSION."/zip/zip".trim($arnuove[$i][0]));
						$messaggi[]="Aggiorno: zip".$arnuove[$i][0]." ";
					}else{
						if (trim($arnuove[$i][0])=="sipadxusr.exe"){
							$messaggi[]="Attenzione! Non trovato il file ".CHECK_PATH."/zip/zip".trim($arnuove[$i][0])." ";
						}
					}
					break;
				case "=":
					if (defined ("SERVER_ESSEITALIA")){
						//richiesto aggiornamento
						$lsql=$arnuove[$i][2];
						updatetable($arnuove[$i][2]);
						copy(CHECK_PATH."/".trim($arnuove[$i][0]),LASTVERSION."/".trim($arnuove[$i][0]));
						$messaggi[]="Aggiorno: ".$arnuove[$i][0]." versione ".$arnuove[$i][1];
						//Caso file zippati
						if (file_exists(CHECK_PATH."/zip/zip".trim($arnuove[$i][0]))){
							copy(CHECK_PATH."/zip/zip".trim($arnuove[$i][0]),LASTVERSION."/zip/zip".trim($arnuove[$i][0]));
						}
					}else{
						$messaggi[]="NON necessita aggiornamento Il file ".$arattuali[$j][0]." ";
					}
					break;
				case "<":
					$messaggi[]="OBSOLETO :Il file ".$arattuali[$j][0]." ";
					break;
				default:
					$messaggi[]="ANOMALO: Il file ".$arattuali[$j][0]." ";
					break;
			}
			break;
		}
	}
	if ($boofound==false){
		//� un file nuovo
		$lsql=$arnuove[$i][2];
		updatetable($arnuove[$i][2]);
		copy(CHECK_PATH."/".trim($arnuove[$i][0]),LASTVERSION."/".trim($arnuove[$i][0]));
		$messaggi[]="Aggiorno: ".$arnuove[$i][0]." versione ".$arnuove[$i][1];
		//Caso file zippati
		if (file_exists(CHECK_PATH."/zip/zip".trim($arnuove[$i][0]))){
			copy(CHECK_PATH."/zip/zip".trim($arnuove[$i][0]),LASTVERSION."/zip/zip".trim($arnuove[$i][0]));
			$messaggi[]="Aggiorno: zip".$arnuove[$i][0]." ";
		}else{
			if (trim($arnuove[$i][0])=="sipadxusr.exe"){
				$messaggi[]="Attenzione! Non trovato il file ".CHECK_PATH."/zip/zip".trim($arnuove[$i][0])." ";
			}
		}
	}
}
for ($i=0;$i<count($messaggi);$i++){
	echo $messaggi[$i]."\n";
}
/*-------------------------------------------------------------------------------------------
 * Ciclo per la verifica delle STAMPE
 -------------------------------------------------------------------------------------------*/
if (CHECK_PATH_OTHER!=""){
	$subpath="rpt";
	$rptattuali=checkdir_other($subpath, "xml");		/* scorre i file della lastversion/rpt */
	$arnuove=checkfile_other();						/* scorre i file descritti nel 0000reg-software.txt*/
	$messaggi=array();
	echo "Nuove stampe: ".count($arnuove)." Nella lastversion/{$subpath}: ".count($rptattuali)."\n";
	for ($i=0;$i<count($arnuove);$i++){
		$boofound=false;
		for ($j=0;$j<count($rptattuali);$j++){
			if (trim($rptattuali[$j][0])==trim($arnuove[$i][0])){
				//stesso nome
				$boofound=true;
				switch (ComparaVersioni($arnuove[$i][1],$rptattuali[$j][1])){
					case ">":
						//richiesto aggiornamento
						$lsql=$arnuove[$i][2];
						updatetable($arnuove[$i][2]);
						copy(CHECK_PATH_OTHER."/".trim($arnuove[$i][0]),LASTVERSION."/{$subpath}/".trim($arnuove[$i][0]));
						$messaggi[]="Aggiorno: ".$arnuove[$i][0]." versione ".$arnuove[$i][1];
						break;
					case "=":
						if (defined ("SERVER_ESSEITALIA")){
							//richiesto aggiornamento
							$lsql=$arnuove[$i][2];
							updatetable($arnuove[$i][2]);
							copy(CHECK_PATH_OTHER."/".trim($arnuove[$i][0]),LASTVERSION."/{$subpath}/".trim($arnuove[$i][0]));
							$messaggi[]="Aggiorno: ".$arnuove[$i][0]." versione ".$arnuove[$i][1];
						}else{
							$messaggi[]="NON necessita aggiornamento Il file ".$rptattuali[$j][0]." ";
						}
						break;
					case "<":
						$messaggi[]="OBSOLETO :Il file ".$rptattuali[$j][0]." ";
						break;
					default:
						$messaggi[]="ANOMALO: Il file ".$rptattuali[$j][0]." ";
						break;
				}
				break;
			}
		}
		if ($boofound==false){
			//� un file nuovo
			$lsql=$arnuove[$i][2];
			updatetable($arnuove[$i][2]);
			copy(CHECK_PATH_OTHER."/".trim($arnuove[$i][0]),LASTVERSION."/{$subpath}/".trim($arnuove[$i][0]));
			$messaggi[]="Aggiorno: ".$arnuove[$i][0]." versione ".$arnuove[$i][1];
			
		}
	}
	if (count($messaggi)>0) {
		echo "----UPDATE RPT----\n";
	}
	for ($i=0;$i<count($messaggi);$i++){
		echo $messaggi[$i]."\n";
	}
}

?>
