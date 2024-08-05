<?php
class db{
//Define Variables

var $link;
var $conn_string;
var $dbgclass;

//class construction
	function db($conn_string)       {
		$this->link = pg_connect ($conn_string);
		if (!$this->link){
			die('Could not connect: ' . pg_last_error());
		}else{
			$this->conn_string=$conn_string;
		}
	}
	function escape($valore,$tipo,$ispk=false){
		switch ($tipo){
			case "long":
			case "integer":
			case "decimal":
				if ((trim($valore)=="")||(!is_numeric($valore)))
					return "NULL";
				else 
					 return $valore;
				break;
			case "text":
			case "string":
				if (($ispk==false)&&($valore=="NULL"))
					return "NULL";
				else 
					return "'".addslashes($valore)."'";
				break;
			case "date":
				if ((trim($valore)=="")||(!isDate($valore)))
					return "NULL";
				else{
					$valore=str_replace("/", "-", $valore);
					if ((substr($valore,2,1)=="-")&&((substr($valore,5,1)=="-"))){
						//il formato � DD-MM-YYYY
						$dateansi=f_formatdateansi($valore);
					}else{
						$dateansi=$valore;
					}
					return "'".$dateansi."'";
				}
				break;
			default:
				Echo "Escape:Caso non gestito:".$tipo.".<BR><hr>";
				break; 
		
		}
	}
	
	function GetMax($codice, $sqlwhere){
		$tsmax=1;
		$arrLista=array();
		$retcode=$this->GetList($arrLista, $sqlwhere);
		if (count($arrLista)>0)
			$tsmax=$this->$codice +1;
		return $tsmax;
	}
	function init($tipo){
		switch ($tipo){
			case "long":
			case "integer":
			case "decimal":
					return 0;
				break;
			case "text":
			case "string":
				return "";
				break;
			case "date":
				return "";
				break;
			default:
				Echo "Escape:Caso non gestito:".$tipo;
				break;
	
		}
	}
	function DbNow(){
		return date("Y-m-d H:i:s"); 

	}
	function newcpccchk(){
		$out=rand(1,100000);
		return "sa".$out;
	}
	function close()
	{
		//pg_close($this->link); NON necessaria anzi pericolosa
	}
	
	function freequery($lsql) {
		$result = dbquery($this->link,$lsql);
	}
	function SetDbgOn($mode="DBG"){
		$this->dbgclass=$mode;
	}
	function Showdbg($sql){
		if ($this->dbgclass=="LOG")
			f_write_logfile($sql,true);
		elseif ($this->dbgclass!="")
			echo $sql."<br>";
	}

	function SetDefault(){
		$this->sys_idutcreazione=1;
		$this->sys_idutulmodifica=1;
		$this->sys_idazcreazione=1;
		$this->sys_datcreazione=date("Y-m-d H:i:s");
		$this->sys_datulmodifica=date("Y-m-d H:i:s");
	}
}