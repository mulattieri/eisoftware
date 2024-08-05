<?php
require "classi/class.db.php";

class tblservappmonitoring001 extends db{
 //Define Variables
	var $flusso;
	var $attivita;
    var $tipocheck;
    var $runprogram;
	var $datalog;
	var $sequenza;
	var $risultato;
	var $messaggio;
	var $flgestito;
	var $tipomail;
	var $extramail;
	var $idazienda;
	var $idsede;
	var $idmarca;
	var $sys_idazcreazione;
	var $sys_idutcreazione;
	var $sys_datcreazione;
	var $sys_idutulmodifica;
	var $sys_datulmodifica;
	var $cpccchk;
    var $commandtext;
    var $idquery;
    var $ulesecuzione;
    var $logei;
	var $codicecallcenter;


 
//class construction
function tblservappmonitoring001($conn_string)       {
	$this->link = pg_connect ($conn_string);
	if (!$this->link){  
		  die('Could not connect: ' . pg_last_error()); 
	}else{
		$this->conn_string=$conn_string;
		$this->flusso=$this->init('string');
		$this->attivita=$this->init('string');
        $this->tipocheck=$this->init('string');
        $this->runprogram=$this->init('string');
		$this->datalog=$this->init('date');
		$this->sequenza=$this->init('integer');
		$this->risultato=$this->init('string');
		$this->messaggio=$this->init('string');
		$this->flgestito=$this->init('integer');
		$this->tipomail=$this->init('string');
		$this->extramail=$this->init('string');
		$this->idazienda=$this->init('integer');
		$this->idsede=$this->init('integer');
		$this->idmarca=$this->init('integer');
		$this->sys_idazcreazione=$this->init('integer');
		$this->sys_idutcreazione=$this->init('integer');
		$this->sys_datcreazione=$this->init('date');
		$this->sys_idutulmodifica=$this->init('integer');
		$this->sys_datulmodifica=$this->init('date');
		$this->cpccchk=$this->init('string');        
        $this->commandtext=$this->init('string');
        $this->idquery=$this->init('integer');
        $this->ulesecuzione=$this->init('date');
        $this->logei=$this->init('integer');
		$this->codicecallcenter=$this->init('string');	
	       }
}
 function Load($flusso, $attivita, $tipocheck, $runprogram, $datalog,  $sequenza) {
		            
		$lsql="SELECT tblservappmonitoring001.*
				FROM tblservappmonitoring001
				WHERE 
                 flusso=".$this->escape($flusso,'string',true)."
				 and attivita=".$this->escape($attivita,'string',true)."
                 and tipocheck=".$this->escape($tipocheck,'string',true)."
                 and runprogram=".$this->escape($runprogram,'string',true)."
				 and datalog=".$this->escape($datalog,'date',true)."
				 and sequenza=".$this->escape($sequenza,'integer',true)."
";
		$this->Showdbg("Load [tblservappmonitoring001]:".$lsql);
		$result = dbquery($this->link,$lsql);
	    $result_num_rows = pg_num_rows($result);
	    if ($result_num_rows==1){
		    $row = pg_fetch_array($result, 0);					
		$this->flusso = $row['flusso'];
		$this->attivita = $row['attivita'];
        $this->tipocheck = $row['tipocheck'];
        $this->runprogram = $row['runprogram'];
		$this->datalog = $row['datalog'];
		$this->sequenza = $row['sequenza'];
		$this->risultato = $row['risultato'];
		$this->messaggio = $row['messaggio'];
		$this->flgestito = $row['flgestito'];
		$this->tipomail = $row['tipomail'];
		$this->extramail = $row['extramail'];
		$this->idazienda = $row['idazienda'];
		$this->idsede = $row['idsede'];
		$this->idmarca = $row['idmarca'];
		$this->sys_idazcreazione = $row['sys_idazcreazione'];
		$this->sys_idutcreazione = $row['sys_idutcreazione'];
		$this->sys_datcreazione = $row['sys_datcreazione'];
		$this->sys_idutulmodifica = $row['sys_idutulmodifica'];
		$this->sys_datulmodifica = $row['sys_datulmodifica'];
		$this->cpccchk = $row['cpccchk'];

			$retcode=0;
	    }else{
	    	$retcode=-1;
	    	//f_write_logfile("LOAD EVENTO".$lsql,true);
	    }
	  	return $retcode;
	}
	
	function Save(){
		$tmpobj=new tblservappmonitoring001($this->conn_string);
		$retcode=$tmpobj->Load( $this->flusso,  $this->attivita, $this->tipocheck, $this->runprogram,  $this->datalog,  $this->sequenza);
		
		if ($retcode==0){
			$retcode=$this->Update();
		}else{
			$retcode=$this->Insert();
			
		}
		return $retcode;
	}
function Update() {
	global $glblog;
		$lsql="UPDATE tblservappmonitoring001 
				SET risultato=".$this->escape($this->risultato,'string')."
			, messaggio=".$this->escape($this->messaggio,'string')."
			, flgestito=".$this->escape($this->flgestito,'integer')."
			, tipomail=".$this->escape($this->tipomail,'string')."
			, extramail=".$this->escape($this->extramail,'string')."
			, idazienda=".$this->escape($this->idazienda,'integer')."
			, idsede=".$this->escape($this->idsede,'integer')."
			, idmarca=".$this->escape($this->idmarca,'integer')."
			, sys_idutulmodifica=".$this->escape($this->sys_idutulmodifica,'integer')."
			, sys_datulmodifica=".$this->escape($this->sys_datulmodifica,'date')."
			, cpccchk=".$this->escape($this->newcpccchk(),'string',false)."

				WHERE 
				 flusso=".$this->escape($this->flusso,'string',true)."
				 and attivita=".$this->escape($this->attivita,'string',true)."
                 and tipocheck=".$this->escape($tipocheck,'string',true)."
                 and runprogram=".$this->escape($runprogram,'string',true)."                 
				 and datalog=".$this->escape($this->datalog,'date',true)."
				 and sequenza=".$this->escape($this->sequenza,'integer',true)."
				 and cpccchk=".$this->escape($this->cpccchk,'string',false)."

				";
		$this->Showdbg("Update [tblservappmonitoring001]:".$lsql);		
		$result = dbquery($this->link,$lsql);
		return pg_affected_rows($result);
}
	
function Insert() {
	global $glblog;
		$lsql="INSERT INTO tblservappmonitoring001(flusso, attivita, tipocheck, runprogram, datalog, sequenza, risultato, messaggio, flgestito, tipomail, extramail, idazienda, idsede, idmarca, sys_idazcreazione, sys_idutcreazione, sys_datcreazione, sys_idutulmodifica, sys_datulmodifica, cpccchk) VALUES (
            ".$this->escape($this->flusso,'string',true)."
			, ".$this->escape($this->attivita,'string',true)."
            , ".$this->escape($this->tipocheck,'string',true)."
            , ".$this->escape($this->runprogram,'string',true)."
			, ".$this->escape($this->datalog,'date',true)."
			, ".$this->escape($this->sequenza,'integer',true)."
			, ".$this->escape($this->risultato,'string',false)."
			, ".$this->escape($this->messaggio,'string',false)."
			, ".$this->escape($this->flgestito,'integer',false)."
			, ".$this->escape($this->tipomail,'string',false)."
			, ".$this->escape($this->extramail,'string',false)."
			, ".$this->escape($this->idazienda,'integer',false)."
			, ".$this->escape($this->idsede,'integer',false)."
			, ".$this->escape($this->idmarca,'integer',false)."
			, ".$this->escape($this->sys_idazcreazione,'integer',false)."
			, ".$this->escape($this->sys_idutcreazione,'integer',false)."
			,  current_timestamp 
			, ".$this->escape($this->sys_idutulmodifica,'integer',false)."
			,  current_timestamp 
			, ".$this->escape($this->newcpccchk(),'string',false)."

				)";
		$this->Showdbg("Insert [tblservappmonitoring001]:".$lsql);
		$result = dbquery($this->link,$lsql);
		return pg_affected_rows($result);
}
function Delete(){
	$lsql="Delete from tblservappmonitoring001 ";
	$lsql.=" WHERE flusso=".$this->escape($this->flusso,'string',true)."
				 and attivita=".$this->escape($this->attivita,'string',true)."
                 and tipocheck=".$this->escape($this->tipocheck,'string',true)."
                 and runprogram=".$this->escape($this->runprogram,'string',true)."
				 and datalog=".$this->escape($this->datalog,'date',true)."
				 and sequenza=".$this->escape($this->sequenza,'integer',true)."
";
	$this->Showdbg("Delete [tblservappmonitoring001]:".$lsql);
	$result = dbquery($this->link,$lsql);
}

function GetList(&$arrLista, $sqlwhere=""){
	$arrLista=array();
	$lsql="SELECT tblservappmonitoring001.*
				FROM tblservappmonitoring001 ";
	if (trim($sqlwhere)!="")			
		$lsql.=" WHERE ".$sqlwhere;

	$this->Showdbg("GetList [tblservappmonitoring001]:".$lsql);
	$result = dbquery($this->link,$lsql);
	$result_num_rows = pg_num_rows($result);
	if ($result_num_rows>0){
		$i=0;
		while ($row = pg_fetch_array ($result)) {
		$this->flusso = $row['flusso'];
		$this->attivita = $row['attivita'];
        $this->tipocheck = $row['tipocheck'];
        $this->runprogram = $row['runprogram'];
		$this->datalog = $row['datalog'];
		$this->sequenza = $row['sequenza'];
		$this->risultato = $row['risultato'];
		$this->messaggio = $row['messaggio'];
		$this->flgestito = $row['flgestito'];
		$this->tipomail = $row['tipomail'];
		$this->extramail = $row['extramail'];
		$this->idazienda = $row['idazienda'];
		$this->idsede = $row['idsede'];
		$this->idmarca = $row['idmarca'];
		$this->sys_idazcreazione = $row['sys_idazcreazione'];
		$this->sys_idutcreazione = $row['sys_idutcreazione'];
		$this->sys_datcreazione = $row['sys_datcreazione'];
		$this->sys_idutulmodifica = $row['sys_idutulmodifica'];
		$this->sys_datulmodifica = $row['sys_datulmodifica'];
		$this->cpccchk = $row['cpccchk'];

			$tsobj=$this;
			$arrLista[]=$tsobj;
			$retcode=0;
		}
	}else{
		$retcode=-1;
		//f_write_logfile("LOAD EVENTO".$lsql,true);
	}
	return $retcode;
}
function setlog(){
	$this->dbgclass="LOG";
}	
}//classe
		
		   
?>